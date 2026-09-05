import { SMTPClient } from 'emailjs';

export async function sendMail({
  to,
  subject,
  text,
}: {
  to: string;
  subject: string;
  text: string;
}) {
  const host = process.env.SMTP_HOST;
  const port = Number(process.env.SMTP_PORT || 587);
  const user = process.env.SMTP_USER;
  const pass = process.env.SMTP_PASSWORD;
  const from = process.env.SMTP_FROM;
  if (!host || !user || !pass || !from) throw new Error('SMTP_NOT_CONFIGURED');
  const client = new SMTPClient({
    host,
    port,
    user,
    password: pass,
    ssl: port === 465,
    tls: port !== 465,
  });
  try {
    await client.sendAsync({ from, to, subject, text });
  } finally {
    client.smtp.close();
  }
}
