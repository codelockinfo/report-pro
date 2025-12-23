import React from 'react';
import ReactDOM from 'react-dom/client';
import { AppProvider } from '@shopify/polaris';
import '@shopify/polaris/build/esm/styles.css';
import { BrowserRouter } from 'react-router-dom';
import App from './App';
import './index.css';
import createApp from '@shopify/app-bridge';

// Get API key from window (injected by server)
const apiKey = (window as any).__SHOPIFY_API_KEY__ || '';

// Get host parameter from URL (required for embedded apps)
const urlParams = new URLSearchParams(window.location.search);
const host = urlParams.get('host') || '';

// Initialize App Bridge for embedded apps
if (apiKey && host && typeof window !== 'undefined') {
  try {
    createApp({
      apiKey: apiKey,
      host: host,
      forceRedirect: true,
    });
  } catch (err) {
    console.warn('App Bridge initialization error:', err);
  }
}

ReactDOM.createRoot(document.getElementById('root')!).render(
  <React.StrictMode>
    <BrowserRouter>
      <AppProvider i18n={{}}>
        <App />
      </AppProvider>
    </BrowserRouter>
  </React.StrictMode>
);

