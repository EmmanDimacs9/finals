# Data Flow Diagram (DFD) - TECHNICIAN MODULE

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                          TECHNICIAN MODULE                                  │
└─────────────────────────────────────────────────────────────────────────────┘

┌──────────────┐                                                              │
│  Technician  │                                                              │
└──────┬───────┘                                                              │
       │                                                                      │
       │ Task Updates                                                        │
       ▼                                                                      │
┌──────────────────────┐              ┌──────────────────────┐              │
│  Task Management     │─────────────▶│  Tasks Database      │              │
│  Process             │◀─────────────│  (tasks table)       │              │
│                      │              └──────────────────────┘              │
└──────────────────────┘                                                      │
       │                                                                      │
       │ Request Processing                                                  │
       ▼                                                                      │
┌──────────────────────┐              ┌──────────────────────┐              │
│  Service Request     │─────────────▶│  Requests Database    │              │
│  Management          │◀─────────────│  (requests table)   │              │
│  Process             │              └──────────────────────┘              │
└──────────────────────┘                                                      │
       │                                                                      │
       │ System Request Processing                                           │
       ▼                                                                      │
┌──────────────────────┐              ┌──────────────────────┐              │
│  System Request      │─────────────▶│  Requests Database    │              │
│  Management          │◀─────────────│  (requests table)   │              │
│  Process             │              └──────────────────────┘              │
└──────────────────────┘                                                      │
       │                                                                      │
       │ Equipment Data                                                      │
       ▼                                                                      │
┌──────────────────────┐              ┌──────────────────────┐              │
│  Equipment Inventory │─────────────▶│  Equipment Database   │              │
│  Management          │◀─────────────│  (desktop, laptops,   │              │
│  Process             │              │   printers, etc.)     │              │
└──────────────────────┘              └──────────────────────┘              │
       │                                                                      │
       │ Maintenance Data                                                    │
       ▼                                                                      │
┌──────────────────────┐              ┌──────────────────────┐              │
│  Maintenance History │─────────────▶│  Maintenance Database │              │
│  Management          │◀─────────────│  (maintenance_records │              │
│  Process             │              │   history table)      │              │
└──────────────────────┘              └──────────────────────┘              │
       │                                                                      │
       │ Statistics/Reports                                                  │
       ▼                                                                      │
┌──────────────────────┐              ┌──────────────────────┐              │
│  Reports Management  │─────────────▶│  Reports Generator    │              │
│  Process             │              │  (PDF Generation)     │              │
└──────────────────────┘              └──────────────────────┘              │
       │                                                                      │
       │ Log Data                                                            │
       ▼                                                                      │
┌──────────────────────┐              ┌──────────────────────┐              │
│  Activity Logs       │─────────────▶│  Logs Database        │              │
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

External Entities:
┌─────────────┐                    ┌─────────────┐
│    Admin    │                    │  Department │
└─────────────┘                    └─────────────┘
     │                                    │
     │ Task Assignments                   │ Request Submissions
     │ Request Approvals                  │ Request Status Updates
     │                                    │
     └────────────────────────────────────┘
```

## Data Stores

- **Tasks Database**: tasks
- **Requests Database**: requests
- **Equipment Database**: desktop, laptops, printers, accesspoint, switch, telephone
- **Maintenance Database**: maintenance_records, history
- **Logs Database**: logs
- **Users Database**: users

## External Entities

- **Technician**: Technical staff processing requests and tasks
- **Admin**: System administrator assigning tasks and approving requests
- **Department**: Department heads submitting requests
