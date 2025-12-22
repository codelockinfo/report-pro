import { useState, useEffect } from 'react';
import {
  Page,
  Card,
  Text,
  Button,
  TextField,
  ResourceList,
  ResourceItem,
  Thumbnail,
  Stack,
  Filters,
  ChoiceList,
  EmptyState,
} from '@shopify/polaris';
import { SearchMinor, Plus } from '@shopify/polaris-icons';
import axios from 'axios';

interface Report {
  id: number;
  name: string;
  category: string;
  description?: string;
  is_custom: boolean;
}

export default function ReportsPage() {
  const [reports, setReports] = useState<Report[]>([]);
  const [loading, setLoading] = useState(true);
  const [searchValue, setSearchValue] = useState('');
  const [selectedCategory, setSelectedCategory] = useState<string[]>([]);

  const categories = [
    'Custom reports',
    'Customers',
    'Products',
    'Product variants',
    'Agreement lines',
    'Orders',
    'Transactions',
    'Inventory levels',
  ];

  useEffect(() => {
    fetchReports();
  }, [searchValue, selectedCategory]);

  const fetchReports = async () => {
    try {
      setLoading(true);
      const params: any = {};
      if (searchValue) params.search = searchValue;
      if (selectedCategory.length > 0) params.category = selectedCategory[0];

      const response = await axios.get('/api/reports', { params });
      setReports(response.data);
    } catch (error) {
      console.error('Failed to fetch reports:', error);
    } finally {
      setLoading(false);
    }
  };

  const filterControl = (
    <Filters
      queryValue={searchValue}
      filters={[]}
      onQueryChange={setSearchValue}
      onQueryClear={() => setSearchValue('')}
      onClearAll={() => {
        setSearchValue('');
        setSelectedCategory([]);
      }}
    >
      <ChoiceList
        title="Category"
        choices={categories.map((cat) => ({ label: cat, value: cat }))}
        selected={selectedCategory}
        onChange={setSelectedCategory}
      />
    </Filters>
  );

  const groupedReports = reports.reduce((acc, report) => {
    const category = report.category || 'Other';
    if (!acc[category]) {
      acc[category] = [];
    }
    acc[category].push(report);
    return acc;
  }, {} as Record<string, Report[]>);

  return (
    <Page
      title="Reports"
      primaryAction={{
        content: 'Create custom report',
        icon: Plus,
        onAction: () => {
          // Handle create report
        },
      }}
    >
      <Card>
        <Stack vertical spacing="loose">
          <TextField
            label="Search by report name..."
            value={searchValue}
            onChange={setSearchValue}
            prefix={<SearchMinor />}
            autoComplete="off"
          />

          {Object.entries(groupedReports).map(([category, categoryReports]) => (
            <Card key={category} sectioned>
              <Stack vertical spacing="tight">
                <Text variant="headingMd" as="h2">
                  {category}
                </Text>
                <ResourceList
                  resourceName={{ singular: 'report', plural: 'reports' }}
                  items={categoryReports}
                  renderItem={(item) => (
                    <ResourceItem
                      id={item.id.toString()}
                      url={`/reports/${item.id}`}
                    >
                      <Text variant="bodyMd" fontWeight="medium" as="span">
                        {item.name}
                      </Text>
                    </ResourceItem>
                  )}
                />
              </Stack>
            </Card>
          ))}

          {reports.length === 0 && !loading && (
            <EmptyState
              heading="No reports found"
              action={{
                content: 'Create custom report',
                onAction: () => {
                  // Handle create report
                },
              }}
            />
          )}
        </Stack>
      </Card>
    </Page>
  );
}

