import { useState } from 'react';
import {
  Page,
  Card,
  Text,
  Button,
  TextField,
  Select,
  Stack,
  DataTable,
  Layout,
} from '@shopify/polaris';

export default function ExplorePage() {
  const [selectedTable, setSelectedTable] = useState('orders');
  const [query, setQuery] = useState('');
  const [results, setResults] = useState<any[]>([]);

  const tableOptions = [
    { label: 'Orders', value: 'orders' },
    { label: 'Products', value: 'products' },
    { label: 'Customers', value: 'customers' },
    { label: 'Transactions', value: 'transactions' },
    { label: 'Agreement Lines', value: 'agreement_lines' },
  ];

  const handleRunQuery = async () => {
    try {
      const response = await fetch('/api/explore/data', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          table: selectedTable,
          filters: {},
          limit: 100,
        }),
      });
      const data = await response.json();
      setResults(data);
    } catch (error) {
      console.error('Failed to run query:', error);
    }
  };

  return (
    <Page title="Explore">
      <Layout>
        <Layout.Section>
          <Card>
            <Stack vertical spacing="loose">
              <Text variant="headingMd" as="h2">
                Explore Data
              </Text>

              <Select
                label="Table"
                options={tableOptions}
                value={selectedTable}
                onChange={setSelectedTable}
              />

              <TextField
                label="Custom Query (SELECT only)"
                value={query}
                onChange={setQuery}
                multiline={4}
                placeholder="SELECT * FROM orders WHERE shop_id = $shopId LIMIT 100"
              />

              <Button primary onClick={handleRunQuery}>
                Run Query
              </Button>

              {results.length > 0 && (
                <DataTable
                  columnContentTypes={Object.keys(results[0]).map(() => 'text')}
                  headings={Object.keys(results[0])}
                  rows={results.map((row) => Object.values(row).map((val) => String(val)))}
                />
              )}
            </Stack>
          </Card>
        </Layout.Section>
      </Layout>
    </Page>
  );
}

