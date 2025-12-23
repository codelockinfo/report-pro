import { Routes, Route, Navigate, useLocation } from 'react-router-dom';
import { Frame, Navigation } from '@shopify/polaris';
import { HomeMajor, SearchMajor, CalendarMajor, SettingsMajor, AnalyticsMajor } from '@shopify/polaris-icons';
import ReportsPage from './pages/Reports';
import ExplorePage from './pages/Explore';
import SchedulePage from './pages/Schedule';
import SettingsPage from './pages/Settings';
import ChartAnalysisPage from './pages/ChartAnalysis';

/**
 * Main App Component
 * Uses Polaris Frame with Navigation for the sidebar menu
 * App Bridge Provider (in main.tsx) handles the embedded app integration
 */
function App() {
  const location = useLocation();

  const navigationMarkup = (
    <Navigation location={location.pathname}>
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
      <Routes>
        <Route path="/" element={<Navigate to="/reports" replace />} />
        <Route path="/reports" element={<ReportsPage />} />
        <Route path="/explore" element={<ExplorePage />} />
        <Route path="/schedule" element={<SchedulePage />} />
        <Route path="/chart-analysis" element={<ChartAnalysisPage />} />
        <Route path="/settings" element={<SettingsPage />} />
      </Routes>
    </Frame>
  );
}

export default App;

