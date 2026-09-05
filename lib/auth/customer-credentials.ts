export const DEVELOPMENT_CUSTOMER_PASSWORD = 'novastra123';

export type CustomerCredentialConfig = {
  username?: string;
  passwordHash?: string;
  plainPassword?: string;
  name?: string;
  email?: string;
  usesDevelopmentDefault: boolean;
};

type CustomerCredentialEnvironment = Record<string, string | undefined>;

export function getCustomerCredentialConfig(
  environment: CustomerCredentialEnvironment = process.env,
  nodeEnvironment = process.env.NODE_ENV,
): CustomerCredentialConfig {
  const usesDevelopmentDefault =
    nodeEnvironment === 'development' && !environment.CUSTOMER_PASSWORD_HASH;

  return {
    username:
      environment.CUSTOMER_USERNAME ||
      (usesDevelopmentDefault ? 'pelanggan' : undefined),
    passwordHash: environment.CUSTOMER_PASSWORD_HASH || undefined,
    plainPassword: usesDevelopmentDefault
      ? DEVELOPMENT_CUSTOMER_PASSWORD
      : undefined,
    name:
      environment.CUSTOMER_NAME ||
      (usesDevelopmentDefault ? 'Pelanggan Novastra' : undefined),
    email:
      environment.CUSTOMER_EMAIL ||
      (usesDevelopmentDefault ? 'pelanggan@novastra.local' : undefined),
    usesDevelopmentDefault,
  };
}

export async function verifyCustomerCredentials(
  config: CustomerCredentialConfig,
  username: string,
  password: string,
) {
  if (
    !config.username ||
    !config.name ||
    !config.email ||
    username !== config.username
  )
    return false;

  if (config.passwordHash) return compare(password, config.passwordHash);
  return Boolean(config.plainPassword && password === config.plainPassword);
}
import { compare } from 'bcryptjs';
