import { useState, useEffect } from 'react';
import {
  Page,
  Card,
  Text,
  Button,
  TextField,
  DataTable,
  Badge,
  Stack,
  Filters,
  ChoiceList,
  Banner,
} from '@shopify/polaris';
import { CalendarMajor } from '@shopify/polaris-icons';
import axios from 'axios';

interface Schedule {
  id: number;
  report_name: string;
  frequency: string;
  time_config: any;
  recipients: any[];
  format: string;
  next_run_at: string;
  enabled: boolean;
  runs_count: number;
}

export default function SchedulePage() {
  const [schedules, setSchedules] = useState<Schedule[]>([]);
  const [loading, setLoading] = useState(true);
  const [searchValue, setSearchValue] = useState('');
  const [statusFilter, setStatusFilter] = useState<string[]>(['all']);
  const [typeFilter, setTypeFilter] = useState<string[]>(['all']);
  const [usage, setUsage] = useState<any>(null);

  useEffect(() => {
    fetchSchedules();
    fetchUsage();
  }, [searchValue, statusFilter, typeFilter]);

  const fetchSchedules = async () => {
    try {
      setLoading(true);
      const params: any = {};
      if (searchValue) params.search = searchValue;
      if (statusFilter[0] !== 'all') params.status = statusFilter[0];
      if (typeFilter[0] !== 'all') params.type = typeFilter[0];

      const response = await axios.get('/api/schedule', { params });
      setSchedules(response.data);
    } catch (error) {
      console.error('Failed to fetch schedules:', error);
    } finally {
      setLoading(false);
    }
  };

  const fetchUsage = async () => {
    try {
      const response = await axios.get('/api/schedule/usage');
      setUsage(response.data);
    } catch (error) {
      console.error('Failed to fetch usage:', error);
    }
  };

  const formatFrequency = (schedule: Schedule) => {
    const { frequency, time_config } = schedule;
    switch (frequency) {
      case 'hourly':
        return `Every hour from ${time_config?.hour || 12}:${String(time_config?.minute || 0).padStart(2, '0')} AM`;
      case 'daily':
        return `Every day at ${time_config?.hour || 12}:${String(time_config?.minute || 0).padStart(2, '0')} ${time_config?.hour >= 12 ? 'PM' : 'AM'}`;
      case 'weekly':
        const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        return `Every ${days[time_config?.dayOfWeek || 0]} at ${time_config?.hour || 12}:${String(time_config?.minute || 0).padStart(2, '0')} ${time_config?.hour >= 12 ? 'PM' : 'AM'}`;
      case 'monthly':
        return `Every month on day ${time_config?.day || 1} at ${time_config?.hour || 12}:${String(time_config?.minute || 0).padStart(2, '0')} ${time_config?.hour >= 12 ? 'PM' : 'AM'}`;
      default:
        return frequency;
    }
  };

  const formatRecipients = (schedule: Schedule) => {
    return schedule.recipients.map((r: any) => r.value || r).join(', ');
  };

  const formatNextRun = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric',
      hour: 'numeric',
      minute: '2-digit',
    });
  };

  const rows = schedules.map((schedule) => [
    schedule.report_name,
    formatFrequency(schedule),
    formatRecipients(schedule),
    formatNextRun(schedule.next_run_at),
    <Badge key={schedule.id} status={schedule.enabled ? 'success' : 'info'}>
      {schedule.enabled ? 'Enabled' : 'Disabled'}
    </Badge>,
    <Button key={`action-${schedule.id}`} size="slim">
      Actions
    </Button>,
  ]);

  return (
    <Page
      title="Schedule"
      primaryAction={{
        content: 'Create Schedule',
        icon: CalendarMajor,
        onAction: () => {
          // Handle create schedule
        },
      }}
    >
      <Stack vertical spacing="loose">
        {usage && (
          <Banner>
            <Text>
              Estimated monthly scheduled runs: <strong>{usage.estimated_monthly_runs} of 1,000</strong>
              {' '}
              <a href="#" onClick={(e) => e.preventDefault()}>
                Learn more
              </a>
            </Text>
          </Banner>
        )}

        <Card>
          <Stack vertical spacing="loose">
            <TextField
              label="Search report, email..."
              value={searchValue}
              onChange={setSearchValue}
              autoComplete="off"
            />

            <Stack>
              <ChoiceList
                title="Status"
                choices={[
                  { label: 'All', value: 'all' },
                  { label: 'Enabled', value: 'enabled' },
                  { label: 'Disabled', value: 'disabled' },
                ]}
                selected={statusFilter}
                onChange={setStatusFilter}
              />
              <ChoiceList
                title="Type"
                choices={[
                  { label: 'All', value: 'all' },
                  { label: 'Email', value: 'email' },
                  { label: 'Google Sheets', value: 'google_sheets' },
                ]}
                selected={typeFilter}
                onChange={setTypeFilter}
              />
            </Stack>

            <DataTable
              columnContentTypes={['text', 'text', 'text', 'text', 'text', 'text']}
              headings={['Report', 'When', 'To', 'Next Run', 'Status', 'Actions']}
              rows={rows}
            />
          </Stack>
        </Card>
      </Stack>
    </Page>
  );
}

