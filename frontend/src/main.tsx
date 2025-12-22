import React from 'react';
import ReactDOM from 'react-dom/client';
import { AppProvider } from '@shopify/polaris';
import { Provider as AppBridgeProvider } from '@shopify/app-bridge-react';
import '@shopify/polaris/build/esm/styles.css';
import { BrowserRouter } from 'react-router-dom';
import App from './App';
import './index.css';

// Get shop and host from URL parameters (Shopify embedded app)
const urlParams = new URLSearchParams(window.location.search);
const shop = urlParams.get('shop') || '';
const host = urlParams.get('host') || '';

// Get API key from environment or window (injected by server)
const apiKey = 
  import.meta.env.VITE_SHOPIFY_API_KEY || 
  (window as any).__SHOPIFY_API_KEY__ ||
  '';

// App Bridge configuration
// Only initialize if we have both API key and host (embedded app)
const config = apiKey && host ? {
  apiKey: apiKey,
  host: host,
  forceRedirect: true,
} : null;

// Render app with or without App Bridge (for embedded vs standalone)
const appContent = config ? (
  <AppBridgeProvider config={config}>
    <AppProvider i18n={{}}>
      <App />
    </AppProvider>
  </AppBridgeProvider>
) : (
  <AppProvider i18n={{}}>
    <App />
  </AppProvider>
);

ReactDOM.createRoot(document.getElementById('root')!).render(
  <React.StrictMode>
    <BrowserRouter>
      {appContent}
    </BrowserRouter>
  </React.StrictMode>
);

