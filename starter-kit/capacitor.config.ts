import { CapacitorConfig } from '@capacitor/cli';

const config: CapacitorConfig = {
  appId: 'com.smiljkovic.ebscharge',
  appName: 'EBS-Charge',
  webDir: 'dist',
  server: {
    androidScheme: 'https'
  }
};

export default config;
