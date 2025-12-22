import React from 'react';
import ReactDOM from 'react-dom/client';
import { AppProvider } from '@shopify/polaris';
import '@shopify/polaris/build/esm/styles.css';
import { BrowserRouter } from 'react-router-dom';
import App from './App';
import './index.css';

// Initialize App Bridge if we're in an embedded app context
const urlParams = new URLSearchParams(window.location.search);
const host = urlParams.get('host') || '';
const apiKey = (window as any).__SHOPIFY_API_KEY__ || '';

// Initialize App Bridge for embedded apps
if (apiKey && host && typeof window !== 'undefined') {
  try {
    // Dynamically import App Bridge and initialize
    import('@shopify/app-bridge').then((appBridge) => {
      appBridge.default({
        apiKey: apiKey,
        host: host,
        forceRedirect: true,
      });
    }).catch((err) => {
      console.warn('Failed to initialize App Bridge:', err);
    });
  } catch (err) {
    console.warn('App Bridge initialization skipped:', err);
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

