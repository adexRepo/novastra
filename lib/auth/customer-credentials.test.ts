import { hash } from 'bcryptjs';
import { describe, expect, it } from 'vitest';
import {
  DEVELOPMENT_CUSTOMER_PASSWORD,
  getCustomerCredentialConfig,
  verifyCustomerCredentials,
} from './customer-credentials';

describe('getCustomerCredentialConfig', () => {
  it('provides the local-only customer account in development', () => {
    expect(getCustomerCredentialConfig({}, 'development')).toEqual({
      username: 'pelanggan',
      passwordHash: undefined,
      plainPassword: DEVELOPMENT_CUSTOMER_PASSWORD,
      name: 'Pelanggan Novastra',
      email: 'pelanggan@novastra.local',
      usesDevelopmentDefault: true,
    });
  });

  it('does not expose default credentials in production', () => {
    expect(getCustomerCredentialConfig({}, 'production')).toEqual({
      username: undefined,
      passwordHash: undefined,
      plainPassword: undefined,
      name: undefined,
      email: undefined,
      usesDevelopmentDefault: false,
    });
  });

  it('uses the configured hashed production identity', () => {
    const config = getCustomerCredentialConfig(
      {
        CUSTOMER_USERNAME: 'customer',
        CUSTOMER_PASSWORD_HASH: '$2b$12$hash',
        CUSTOMER_NAME: 'Customer Utama',
        CUSTOMER_EMAIL: 'customer@example.com',
      },
      'production',
    );

    expect(config).toMatchObject({
      username: 'customer',
      passwordHash: '$2b$12$hash',
      plainPassword: undefined,
      name: 'Customer Utama',
      email: 'customer@example.com',
      usesDevelopmentDefault: false,
    });
  });

  it('accepts only the matching development username and password', async () => {
    const config = getCustomerCredentialConfig({}, 'development');

    await expect(
      verifyCustomerCredentials(config, 'pelanggan', 'novastra123'),
    ).resolves.toBe(true);
    await expect(
      verifyCustomerCredentials(config, 'pelanggan', 'password-salah'),
    ).resolves.toBe(false);
    await expect(
      verifyCustomerCredentials(config, 'admin', 'novastra123'),
    ).resolves.toBe(false);
  });

  it('verifies a configured production bcrypt hash', async () => {
    const passwordHash = await hash('password-produksi', 4);
    const config = getCustomerCredentialConfig(
      {
        CUSTOMER_USERNAME: 'customer',
        CUSTOMER_PASSWORD_HASH: passwordHash,
        CUSTOMER_NAME: 'Customer Utama',
        CUSTOMER_EMAIL: 'customer@example.com',
      },
      'production',
    );

    await expect(
      verifyCustomerCredentials(config, 'customer', 'password-produksi'),
    ).resolves.toBe(true);
    await expect(
      verifyCustomerCredentials(config, 'customer', 'password-salah'),
    ).resolves.toBe(false);
  });
});
