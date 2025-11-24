# Data Flow Diagram (DFD) - DEPARTMENT MODULE

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                          DEPARTMENT MODULE                                    │
└─────────────────────────────────────────────────────────────────────────────┘

┌──────────────┐                                                              │
│  Department  │                                                              │
└──────┬───────┘                                                              │
       │                                                                      │
       │ Request Data                                                        │
       ▼                                                                      │
┌──────────────────────┐              ┌──────────────────────┐              │
│  Service Request     │─────────────▶│  Requests Database   │              │
│  Management          │◀─────────────│  (requests table)   │              │
│  Process             │              └──────────────────────┘              │
└──────────────────────┘                                                      │
       │                                                                      │
       │ System Request Data                                                 │
       ▼                                                                      │
┌──────────────────────┐              ┌──────────────────────┐              │
│  System Request      │─────────────▶│  Requests Database   │              │
│  Management          │◀─────────────│  (requests table)   │              │
│  Process             │              └──────────────────────┘              │
└──────────────────────┘                                                      │
       │                                                                      │
       │ Maintenance Plan Data                                                │
       ▼                                                                      │
┌──────────────────────┐              ┌──────────────────────┐              │
│  Preventive          │─────────────▶│  Maintenance Plans   │              │
│  Maintenance Plan    │◀─────────────│  Database            │              │
│  Management          │              │  (preventive_plans   │              │
│  Process             │              │   table)             │              │
└──────────────────────┘              └──────────────────────┘              │
       │                                                                      │
       │ Checklist Data                                                      │
       ▼                                                                      │
┌──────────────────────┐              ┌──────────────────────┐              │
│  Checklist           │─────────────▶│  Checklist Database │              │
│  Management          │◀─────────────│  (checklist,         │              │
│  Process             │              │   checklist_categories│              │
└──────────────────────┘              └──────────────────────┘              │
       │                                                                      │
       │ Remarks Data                                                        │
       ▼                                                                      │
┌──────────────────────┐              ┌──────────────────────┐              │
│  Remarks Management  │─────────────▶│  Remarks Database    │              │
│  Process             │◀─────────────│  (remarks table)     │              │
└──────────────────────┘              └──────────────────────┘              │
       │                                                                      │
       │ Statistics/Reports                                                  │
       ▼                                                                      │
┌──────────────────────┐              ┌──────────────────────┐              │
│  Dashboard           │─────────────▶│  Statistics          │              │
│  Management          │              │  Aggregator           │              │
│  Process             │              └──────────────────────┘              │
└──────────────────────┘                                                      │
       │                                                                      │
       │ Log Data                                                            │
       ▼                                                                      │
┌──────────────────────┐              ┌──────────────────────┐              │
│  Activity Logs       │─────────────▶│  Logs Database       │              │
│  Management          │◀─────────────│  (logs table)        │              │
│  Process             │              └──────────────────────┘              │
└──────────────────────┘                                                      │
       │                                                                      │
       │ Profile Data                                                        │
       ▼                                                                      │
┌──────────────────────┐              ┌──────────────────────┐              │
│  Profile Management  │─────────────▶│  Users Database       │              │
│  Process             │◀─────────────│  (users table)       │              │
└──────────────────────┘              └──────────────────────┘              │
       │                                                                      │
       │ Equipment Data                                                      │
       ▼                                                                      │
┌──────────────────────┐              ┌──────────────────────┐              │
│  Equipment           │─────────────▶│  Equipment Database   │              │
│  Management          │◀─────────────│  (desktop, laptops,   │              │
│  Process             │              │   printers, etc.)     │              │
└──────────────────────┘              └──────────────────────┘              │
       │                                                                      │
       │ PDF Generation                                                      │
       ▼                                                                      │
┌──────────────────────┐                                                      │
│  PDF Generator       │                                                      │
│  (Request PDFs,      │                                                      │
│   Maintenance Plans) │                                                      │
└──────────────────────┘                                                      │

External Entities:
┌─────────────┐                    ┌─────────────┐
│    Admin    │                    │  Technician │
└─────────────┘                    └─────────────┘
     │                                    │
     │ Request Approvals                 │ Request Processing
     │ Task Assignments                  │ Task Updates
     │ Request Status                    │ Request Status Updates
     │                                    │
     └────────────────────────────────────┘
```

## Data Stores

- **Requests Database**: requests
- **Maintenance Plans Database**: preventive_plans
- **Checklist Database**: checklist, checklist_categories
- **Remarks Database**: remarks
- **Logs Database**: logs
- **Users Database**: users
- **Equipment Database**: desktop, laptops, printers, accesspoint, switch, telephone

## External Entities

- **Department**: Department heads submitting requests and plans
- **Admin**: System administrator approving/rejecting requests
- **Technician**: Technical staff processing requests and tasks
