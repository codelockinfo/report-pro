import { useState, useEffect } from 'react';
import {
  Page,
  Card,
  Text,
  Button,
  Stack,
  Select,
  TextField,
  Layout,
  ResourceList,
  EmptyState,
} from '@shopify/polaris';
import { Plus } from '@shopify/polaris-icons';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  ArcElement,
  Title,
  Tooltip,
  Legend,
} from 'chart.js';
import { Line, Bar, Pie, Doughnut } from 'react-chartjs-2';
import axios from 'axios';

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  ArcElement,
  Title,
  Tooltip,
  Legend
);

interface Chart {
  id: number;
  name: string;
  chart_type: string;
  config: any;
}

export default function ChartAnalysisPage() {
  const [charts, setCharts] = useState<Chart[]>([]);
  const [selectedChart, setSelectedChart] = useState<Chart | null>(null);
  const [chartData, setChartData] = useState<any>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchCharts();
  }, []);

  useEffect(() => {
    if (selectedChart) {
      fetchChartData();
    }
  }, [selectedChart]);

  const fetchCharts = async () => {
    try {
      const response = await axios.get('/api/chart-analysis');
      setCharts(response.data);
      if (response.data.length > 0 && !selectedChart) {
        setSelectedChart(response.data[0]);
      }
    } catch (error) {
      console.error('Failed to fetch charts:', error);
    } finally {
      setLoading(false);
    }
  };

  const fetchChartData = async () => {
    if (!selectedChart) return;

    try {
      setLoading(true);
      const response = await axios.post('/api/chart-analysis/data', {
        chartType: selectedChart.chart_type,
        ...selectedChart.config,
      });
      setChartData(response.data);
    } catch (error) {
      console.error('Failed to fetch chart data:', error);
    } finally {
      setLoading(false);
    }
  };

  const renderChart = () => {
    if (!chartData || !selectedChart) return null;

    const commonData = {
      labels: chartData.labels || [],
      datasets: chartData.datasets || [],
    };

    const options = {
      responsive: true,
      plugins: {
        legend: {
          position: 'top' as const,
        },
        title: {
          display: true,
          text: selectedChart.name,
        },
      },
    };

    switch (selectedChart.chart_type) {
      case 'line':
        return <Line data={commonData} options={options} />;
      case 'bar':
        return <Bar data={commonData} options={options} />;
      case 'pie':
        return <Pie data={commonData} options={options} />;
      case 'doughnut':
        return <Doughnut data={commonData} options={options} />;
      default:
        return <Line data={commonData} options={options} />;
    }
  };

  return (
    <Page
      title="Chart Analysis"
      primaryAction={{
        content: 'Create Chart',
        icon: Plus,
        onAction: () => {
          // Handle create chart
        },
      }}
    >
      <Layout>
        <Layout.Section secondary>
          <Card>
            <Stack vertical spacing="loose">
              <Text variant="headingMd" as="h2">
                Charts
              </Text>
              <ResourceList
                resourceName={{ singular: 'chart', plural: 'charts' }}
                items={charts}
                renderItem={(item) => (
                  <ResourceItem
                    id={item.id.toString()}
                    onClick={() => setSelectedChart(item)}
                    accessibilityLabel={`View ${item.name}`}
                  >
                    <Text variant="bodyMd" fontWeight="medium" as="span">
                      {item.name}
                    </Text>
                    <div>
                      <Text tone="subdued" as="span">
                        {item.chart_type}
                      </Text>
                    </div>
                  </ResourceItem>
                )}
                emptyState={
                  <EmptyState
                    heading="No charts yet"
                    action={{
                      content: 'Create Chart',
                      onAction: () => {
                        // Handle create chart
                      },
                    }}
                  />
                }
              />
            </Stack>
          </Card>
        </Layout.Section>

        <Layout.Section>
          <Card>
            {selectedChart ? (
              <Stack vertical spacing="loose">
                <Text variant="headingMd" as="h2">
                  {selectedChart.name}
                </Text>
                {renderChart()}
              </Stack>
            ) : (
              <EmptyState
                heading="Select a chart to view"
                action={{
                  content: 'Create Chart',
                  onAction: () => {
                    // Handle create chart
                  },
                }}
              />
            )}
          </Card>
        </Layout.Section>
      </Layout>
    </Page>
  );
}

