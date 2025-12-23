import { useState, useEffect } from 'react';
import {
  Page,
  Card,
  Text,
  TextField,
  ResourceList,
  ResourceItem,
  BlockStack,
  EmptyState,
} from '@shopify/polaris';
import { SearchMinor, CirclePlusOutlineMinor } from '@shopify/polaris-icons';
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
  // Category filter available for future use
  // const [selectedCategory, setSelectedCategory] = useState<string[]>([]);

  // Categories available for future filter functionality
  // const categories = [
  //   'Custom reports',
  //   'Customers',
  //   'Products',
  //   'Product variants',
  //   'Agreement lines',
  //   'Orders',
  //   'Transactions',
  //   'Inventory levels',
  // ];

  useEffect(() => {
    fetchReports();
  }, [searchValue]);

  const fetchReports = async () => {
    try {
      setLoading(true);
      const params: any = {};
      if (searchValue) params.search = searchValue;
      // Category filter available for future use
      // if (selectedCategory.length > 0) params.category = selectedCategory[0];

      const response = await axios.get('/api/reports', { params });
      setReports(response.data);
    } catch (error) {
      console.error('Failed to fetch reports:', error);
    } finally {
      setLoading(false);
    }
  };

  // Filter control available for future use
  // const filterControl = (
  //   <Filters
  //     queryValue={searchValue}
  //     filters={[]}
  //     onQueryChange={setSearchValue}
  //     onQueryClear={() => setSearchValue('')}
  //     onClearAll={() => {
  //       setSearchValue('');
  //       setSelectedCategory([]);
  //     }}
  //   >
  //     <ChoiceList
  //       title="Category"
  //       choices={categories.map((cat) => ({ label: cat, value: cat }))}
  //       selected={selectedCategory}
  //       onChange={setSelectedCategory}
  //     />
  //   </Filters>
  // );

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
        icon: CirclePlusOutlineMinor,
        onAction: () => {
          // Handle create report
        },
      }}
    >
      <Card>
        <BlockStack gap="400">
          <TextField
            label="Search by report name..."
            value={searchValue}
            onChange={setSearchValue}
            prefix={<SearchMinor />}
            autoComplete="off"
          />

          {Object.entries(groupedReports).map(([category, categoryReports]) => (
            <Card key={category}>
              <BlockStack gap="200">
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
              </BlockStack>
            </Card>
          ))}

          {reports.length === 0 && !loading && (
            <EmptyState
              image="https://cdn.shopify.com/s/files/1/0757/9955/files/empty-state.svg"
              heading="No reports found"
              action={{
                content: 'Create custom report',
                onAction: () => {
                  // Handle create report
                },
              }}
            />
          )}
        </BlockStack>
      </Card>
    </Page>
  );
}

