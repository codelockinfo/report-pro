import { useState, useEffect } from 'react';
import {
  Page,
  Card,
  Text,
  Button,
  Select,
  BlockStack,
  Layout,
  Tabs,
} from '@shopify/polaris';
import axios from 'axios';

interface Settings {
  week_start: string;
  locale: string;
  timezone: string;
}

export default function SettingsPage() {
  const [settings, setSettings] = useState<Settings>({
    week_start: 'sunday',
    locale: 'en-US',
    timezone: 'UTC',
  });
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [selectedTab, setSelectedTab] = useState(0);

  useEffect(() => {
    fetchSettings();
  }, []);

  const fetchSettings = async () => {
    try {
      const response = await axios.get('/api/settings');
      setSettings(response.data);
    } catch (error) {
      console.error('Failed to fetch settings:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleSave = async () => {
    try {
      setSaving(true);
      await axios.put('/api/settings', settings);
      // Show success message
    } catch (error) {
      console.error('Failed to save settings:', error);
    } finally {
      setSaving(false);
    }
  };

  const tabs = [
    {
      id: 'general',
      content: 'General',
      panelID: 'general-panel',
    },
    {
      id: 'schedules',
      content: 'Schedules',
      panelID: 'schedules-panel',
    },
    {
      id: 'integrations',
      content: 'Integrations',
      panelID: 'integrations-panel',
    },
  ];

  const weekOptions = [
    { label: 'Sunday to Saturday', value: 'sunday' },
    { label: 'Monday to Sunday', value: 'monday' },
  ];

  const localeOptions = [
    { label: 'United States (en-US)', value: 'en-US' },
    { label: 'United Kingdom (en-GB)', value: 'en-GB' },
    { label: 'India (en-IN)', value: 'en-IN' },
    { label: 'Canada (en-CA)', value: 'en-CA' },
    { label: 'Australia (en-AU)', value: 'en-AU' },
  ];

  return (
    <Page title="Settings">
      <Layout>
        <Layout.Section>
          <Card>
            <Tabs tabs={tabs} selected={selectedTab} onSelect={setSelectedTab}>
              {selectedTab === 0 && (
                <Card>
                  <BlockStack gap="400">
                    <Text variant="headingMd" as="h2">
                      General settings
                    </Text>

                    <Select
                      label="Week"
                      options={weekOptions}
                      value={settings.week_start}
                      onChange={(value) => setSettings({ ...settings, week_start: value })}
                    />
                    <Text tone="subdued" as="p">
                      Used when segmenting (e.g. Total sales by week) and filtering (e.g Last 4 weeks)
                    </Text>

                    <Select
                      label="Locale"
                      options={localeOptions}
                      value={settings.locale}
                      onChange={(value) => setSettings({ ...settings, locale: value })}
                    />
                    <Text tone="subdued" as="p">
                      For formatting dates and numbers
                    </Text>

                    <Button variant="primary" onClick={handleSave} loading={saving}>
                      Save
                    </Button>
                  </BlockStack>
                </Card>
              )}

              {selectedTab === 1 && (
                <Card>
                  <Text as="p">Schedule settings coming soon...</Text>
                </Card>
              )}

              {selectedTab === 2 && (
                <Card>
                  <Text as="p">Integrations settings coming soon...</Text>
                </Card>
              )}
            </Tabs>
          </Card>
        </Layout.Section>
      </Layout>
    </Page>
  );
}

