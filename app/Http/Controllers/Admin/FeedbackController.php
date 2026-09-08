<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\Order;
use App\Services\FeedbackEvidenceStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class FeedbackController extends Controller
{
    public function index(): View
    {
        return view('admin.feedback.index', [
            'feedback' => Feedback::with(['order', 'testimonial'])->latest()->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.feedback.form', [
            'feedbackItem' => new Feedback(['source' => 'WHATSAPP']),
            'orders' => Order::latest()->limit(100)->get(['id', 'order_number']),
        ]);
    }

    public function store(Request $request, FeedbackEvidenceStorage $storage): RedirectResponse
    {
        $data = $this->validated($request);
        $screenshot = null;

        try {
            if ($request->hasFile('screenshot')) {
                $screenshot = $storage->save($request->file('screenshot'));
                $data['screenshot_path'] = $screenshot;
            }

            Feedback::create([...$data, 'source' => 'WHATSAPP']);
        } catch (RuntimeException) {
            $storage->delete($screenshot);

            return back()->withInput()->withErrors(['screenshot' => 'Screenshot tidak valid atau gagal disimpan.']);
        } catch (Throwable $error) {
            $storage->delete($screenshot);
            throw $error;
        }

        return redirect()->route('admin.feedback.index')->with('success', 'Feedback WhatsApp ditambahkan.');
    }

    public function edit(Feedback $feedback): View
    {
        $this->ensureWhatsApp($feedback);

        return view('admin.feedback.form', [
            'feedbackItem' => $feedback,
            'orders' => Order::latest()->limit(100)->get(['id', 'order_number']),
        ]);
    }

    public function update(Request $request, Feedback $feedback, FeedbackEvidenceStorage $storage): RedirectResponse
    {
        $this->ensureWhatsApp($feedback);
        $data = $this->validated($request);
        $oldScreenshot = $feedback->screenshot_path;
        $newScreenshot = null;

        try {
            if ($request->hasFile('screenshot')) {
                $newScreenshot = $storage->save($request->file('screenshot'));
                $data['screenshot_path'] = $newScreenshot;
            }

            $feedback->update($data);
        } catch (RuntimeException) {
            $storage->delete($newScreenshot);

            return back()->withInput()->withErrors(['screenshot' => 'Screenshot tidak valid atau gagal disimpan.']);
        } catch (Throwable $error) {
            $storage->delete($newScreenshot);
            throw $error;
        }

        if ($newScreenshot) {
            $storage->delete($oldScreenshot);
        }

        return redirect()->route('admin.feedback.index')->with('success', 'Feedback WhatsApp diperbarui.');
    }

    public function destroy(Feedback $feedback, FeedbackEvidenceStorage $storage): RedirectResponse
    {
        $this->ensureWhatsApp($feedback);

        if ($feedback->testimonial()->exists()) {
            throw ValidationException::withMessages([
                'feedback' => 'Feedback tidak dapat dihapus karena masih menjadi sumber testimoni. Hapus testimoni terlebih dahulu.',
            ]);
        }

        $path = $feedback->screenshot_path;
        $feedback->delete();
        $storage->delete($path);

        return redirect()->route('admin.feedback.index')->with('success', 'Feedback WhatsApp dihapus.');
    }

    public function screenshot(Feedback $feedback, FeedbackEvidenceStorage $storage): BinaryFileResponse
    {
        abort_unless($feedback->source === 'WHATSAPP' && $feedback->screenshot_path, 404);
        $path = $storage->path($feedback->screenshot_path);
        abort_unless(File::isFile($path), 404);

        return response()->file($path, ['X-Content-Type-Options' => 'nosniff']);
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:160'],
            'rating' => ['nullable', 'integer', 'between:1,5'],
            'message' => ['required', 'string', 'max:1000'],
            'order_id' => ['nullable', 'integer', Rule::exists('orders', 'id')],
            'occurred_at' => ['nullable', 'date'],
            'screenshot' => ['nullable', 'file', 'max:2048', 'mimetypes:image/jpeg,image/png,image/webp'],
        ]);
        unset($data['screenshot']);

        return $data;
    }

    private function ensureWhatsApp(Feedback $feedback): void
    {
        abort_unless($feedback->source === 'WHATSAPP', 403);
    }
}
