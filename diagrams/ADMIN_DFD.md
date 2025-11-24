# Data Flow Diagram (DFD) - ADMIN MODULE

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                              ADMIN MODULE                                    │
└─────────────────────────────────────────────────────────────────────────────┘

┌──────────────┐                                                              │
│   Admin      │                                                              │
└──────┬───────┘                                                              │
       │                                                                      │
       │ Equipment Data                                                      │
       ▼                                                                      │
┌──────────────────────┐              ┌──────────────────────┐              │
│  Equipment           │─────────────▶│  Equipment Database   │              │
│  Management          │◀─────────────│  (desktop, laptops,   │              │
│  Process             │              │   printers, accesspoint│              │
│                      │              │   switch, telephone)  │              │
└──────────────────────┘              └──────────────────────┘              │
       │                                                                      │
       │ User Data                                                           │
       ▼                                                                      │
┌──────────────────────┐              ┌──────────────────────┐              │
│  User Management     │─────────────▶│  Users Database      │              │
│  Process             │◀─────────────│  (users table)       │              │
└──────────────────────┘              └──────────────────────┘              │
       │                                                                      │
       │ Request Data                                                        │
       ▼                                                                      │
┌──────────────────────┐              ┌──────────────────────┐              │
│  Request Management  │─────────────▶│  Requests Database   │              │
│  Process             │◀─────────────│  (requests table)    │              │
└──────────────────────┘              └──────────────────────┘              │
       │                                                                      │
       │ Maintenance Data                                                    │
       ▼                                                                      │
┌──────────────────────┐              ┌──────────────────────┐              │
│  Maintenance         │─────────────▶│  Maintenance Database│              │
│  Management         │◀─────────────│  (maintenance_records│              │
│  Process            │              │   table)             │              │
└──────────────────────┘              └──────────────────────┘              │
       │                                                                      │
       │ Task Assignment Data                                                │
       ▼                                                                      │
┌──────────────────────┐              ┌──────────────────────┐              │
│  Task Assignment    │─────────────▶│  Tasks Database      │              │
│  Process            │◀─────────────│  (tasks table)       │              │
└──────────────────────┘              └──────────────────────┘              │
       │                                                                      │
       │ Statistics/Reports                                                  │
       ▼                                                                      │
┌──────────────────────┐              ┌──────────────────────┐              │
│  Reports Management  │─────────────▶│  Reports Generator    │              │
│  Process            │              │  (PDF Generation)     │              │
└──────────────────────┘              └──────────────────────┘              │
       │                                                                      │
       │ Log Data                                                            │
       ▼                                                                      │
┌──────────────────────┐              ┌──────────────────────┐              │
│  System Logs         │─────────────▶│  Logs Database        │              │
│  Management          │◀─────────────│  (logs table)        │              │
│  Process             │              └──────────────────────┘              │
└──────────────────────┘                                                      │
       │                                                                      │
       │ Dashboard Data                                                      │
       ▼                                                                      │
┌──────────────────────┐                                                      │
│  Dashboard           │                                                      │
│  Management          │                                                      │
│  Process             │                                                      │
└──────────────────────┘                                                      │

External Entities:
┌─────────────┐                    ┌─────────────┐
│  Technician │                    │  Department │
└─────────────┘                    └─────────────┘
     ▲                                    ▲
     │                                    │
     │ Task Assignments                   │ Request Submissions
     │ Request Notifications              │ Request Status
     │                                    │
     └────────────────────────────────────┘
```

## Data Stores

- **Equipment Database**: desktop, laptops, printers, accesspoint, switch, telephone
- **Users Database**: users
- **Requests Database**: requests
- **Maintenance Database**: maintenance_records
- **Tasks Database**: tasks
- **Logs Database**: logs

## External Entities

- **Admin**: System administrator managing all modules
- **Technician**: Technical staff receiving task assignments
- **Department**: Department heads submitting requests
