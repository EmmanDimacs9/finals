# Functional Decomposition Diagram (FDD) - TECHNICIAN MODULE

```
Technician System
├── Task Management
│   ├── View Tasks (Kanban Board)
│   │   ├── View Pending Tasks
│   │   ├── View In Progress Tasks
│   │   ├── View Completed Tasks
│   │   └── Filter by Request Type
│   ├── Update Task Status
│   │   ├── Move to Pending
│   │   ├── Move to In Progress
│   │   ├── Move to Completed
│   │   └── Update Task Priority
│   ├── Task Operations
│   │   ├── View Task Details
│   │   ├── Add Task Remarks
│   │   ├── Update Task Remarks
│   │   └── Complete Task with Remarks
│   └── Task Statistics
│       ├── View Assigned Tasks Count
│       ├── View Completed Tasks Count
│       └── View Task Progress
├── Service Request Management
│   ├── View Service Requests
│   │   ├── List All Service Requests
│   │   ├── Filter by Status
│   │   ├── View Request Details
│   │   └── View Request Form Data
│   ├── Process Service Requests
│   │   ├── Accept Service Request
│   │   ├── Update Service Request Status
│   │   ├── Add Service Request Remarks
│   │   └── Complete Service Request
│   └── Service Request Operations
│       ├── View Request History
│       └── Generate Service Request Report
├── System Request Management
│   ├── View System Requests
│   │   ├── List All System Requests
│   │   ├── Filter by Status
│   │   ├── View Request Details
│   │   └── View Request Form Data
│   ├── Process System Requests
│   │   ├── Accept System Request
│   │   ├── Update System Request Status
│   │   ├── Add System Request Remarks
│   │   └── Complete System Request
│   └── System Request Operations
│       ├── View Request History
│       └── Generate System Request Report
├── Equipment Inventory Management
│   ├── View Equipment Inventory
│   │   ├── List All Equipment
│   │   ├── Filter by Category
│   │   ├── Filter by Department
│   │   ├── Filter by Location
│   │   ├── Search Equipment
│   │   └── View Equipment Details
│   ├── Equipment Operations
│   │   ├── View Equipment Specifications
│   │   ├── View Equipment Location
│   │   ├── View Equipment Assignment
│   │   └── Generate Equipment QR Code
│   └── Equipment Statistics
│       ├── View Assigned Equipment Count
│       └── View Equipment by Status
├── Maintenance History Management
│   ├── View Maintenance History
│   │   ├── List All Maintenance Records
│   │   ├── Filter by Date Range
│   │   ├── Filter by Equipment
│   │   ├── View Maintenance Details
│   │   └── View Maintenance Remarks
│   ├── Add Maintenance Record
│   │   ├── Record Maintenance Activity
│   │   ├── Add Maintenance Remarks
│   │   └── Update Equipment Status
│   └── Maintenance Operations
│       ├── View Maintenance Statistics
│       └── Generate Maintenance Report
├── Reports Management
│   ├── Generate Task Reports
│   │   ├── Task Completion Report
│   │   └── Task Performance Report
│   ├── Generate Maintenance Reports
│   │   └── Maintenance Activity Report
│   └── Report Operations
│       ├── Export to PDF
│       ├── Filter by Date Range
│       └── Filter by Equipment
├── Activity Logs Management
│   ├── View Activity Logs
│   │   ├── List All Activities
│   │   ├── Filter by Date Range
│   │   ├── Filter by Action Type
│   │   └── View Activity Details
│   └── Activity Operations
│       ├── Search Activities
│       └── View Activity Statistics
├── Profile Management
│   ├── View Profile
│   │   ├── View Personal Information
│   │   ├── View Profile Image
│   │   └── View Account Details
│   ├── Update Profile
│   │   ├── Update Personal Information
│   │   ├── Update Profile Image
│   │   └── Change Password
│   └── Profile Operations
│       └── View Profile Statistics
└── Statistics Management
    ├── View Equipment Statistics
    │   └── Count Assigned Equipment
    ├── View Task Statistics
    │   └── Count Assigned Tasks
    └── View Maintenance Statistics
        └── Count Maintenance Records
```
