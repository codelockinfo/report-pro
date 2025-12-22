import { Routes, Route, Navigate } from 'react-router-dom';
import { Frame, Navigation } from '@shopify/polaris';
import { HomeMajor, SearchMajor, CalendarMajor, SettingsMajor, AnalyticsMajor } from '@shopify/polaris-icons';
import ReportsPage from './pages/Reports';
import ExplorePage from './pages/Explore';
import SchedulePage from './pages/Schedule';
import SettingsPage from './pages/Settings';
import ChartAnalysisPage from './pages/ChartAnalysis';
import Layout from './components/Layout';

function App() {
  const navigationMarkup = (
    <Navigation location="/">
      <Navigation.Section
        items={[
          {
            label: 'Reports',
            url: '/reports',
            icon: HomeMajor,
          },
          {
            label: 'Explore',
            url: '/explore',
            icon: SearchMajor,
          },
          {
            label: 'Schedule',
            url: '/schedule',
            icon: CalendarMajor,
          },
          {
            label: 'Chart Analysis',
            url: '/chart-analysis',
            icon: AnalyticsMajor,
          },
          {
            label: 'Settings',
            url: '/settings',
            icon: SettingsMajor,
          },
        ]}
      />
    </Navigation>
  );

  return (
    <Frame navigation={navigationMarkup}>
      <Layout>
        <Routes>
          <Route path="/" element={<Navigate to="/reports" replace />} />
          <Route path="/reports" element={<ReportsPage />} />
          <Route path="/explore" element={<ExplorePage />} />
          <Route path="/schedule" element={<SchedulePage />} />
          <Route path="/chart-analysis" element={<ChartAnalysisPage />} />
          <Route path="/settings" element={<SettingsPage />} />
        </Routes>
      </Layout>
    </Frame>
  );
}

export default App;

