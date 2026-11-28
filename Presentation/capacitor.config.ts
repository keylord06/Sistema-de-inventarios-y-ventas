import type { CapacitorConfig } from '@capacitor/cli';

const config: CapacitorConfig = {
  appId: 'com.grupo.inventarioventas',
  appName: 'Inventario y Ventas',
  webDir: 'dist/Presentation/browser',
  server: {
    cleartext: true,
    androidScheme: 'http'
  }
};

export default config;
