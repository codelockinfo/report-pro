import { ReactNode } from 'react';
import { Banner } from '@shopify/polaris';

interface LayoutProps {
  children: ReactNode;
}

export default function Layout({ children }: LayoutProps) {
  return (
    <>
      <Banner
        title="Better Reports is making improvements!"
        tone="info"
      >
        <p>
          The <strong>Order lines</strong> table has been renamed to <strong>Agreement lines</strong>.
          {' '}
          <a href="#" onClick={(e) => e.preventDefault()}>
            Read the <strong>full announcement</strong> of upcoming changes.
          </a>
        </p>
      </Banner>
      {children}
    </>
  );
}

