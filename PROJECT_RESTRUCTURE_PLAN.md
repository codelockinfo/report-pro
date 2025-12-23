# Project Restructure Plan

## Current Structure Issues
- Mixed frontend/backend organization
- Could use Shopify CLI patterns better
- App Bridge setup can be cleaner

## New Structure (Shopify CLI Inspired)
```
report-pro/
├── web/                      # Frontend (React + Polaris)
│   ├── src/
│   │   ├── components/
│   │   ├── pages/
│   │   ├── App.tsx
│   │   └── main.tsx
│   └── package.json
├── server/                   # Backend (Node.js + Express)
│   ├── api/
│   │   ├── auth/
│   │   ├── reports/
│   │   ├── explore/
│   │   ├── schedule/
│   │   ├── charts/
│   │   └── settings/
│   ├── lib/
│   │   ├── db/
│   │   ├── shopify/
│   │   └── services/
│   └── server.ts
├── database_setup.sql
├── package.json
└── README.md
```

## Migration Strategy
1. Create new folder structure
2. Move and organize all code
3. Update imports and paths
4. Test build and functionality
5. Replace old structure

