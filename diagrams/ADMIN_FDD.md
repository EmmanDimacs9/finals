# Functional Decomposition Diagram (FDD) - ADMIN MODULE

```
Admin System
├── Dashboard Management
│   ├── View Statistics (Total Equipment, Working Units, Not Working Units, Total Departments)
│   ├── View Category Distribution
│   └── View Department Distribution
├── Equipment Management
│   ├── Add Equipment
│   │   ├── Validate Asset Tag
│   │   ├── Add Desktop
│   │   ├── Add Laptop
│   │   ├── Add Printer
│   │   ├── Add Access Point
│   │   ├── Add Switch
│   │   └── Add Telephone
│   ├── Edit Equipment
│   │   ├── Update Equipment Details
│   │   ├── Update Specifications
│   │   └── Update Location/Assignment
│   ├── Delete Equipment
│   │   └── Remove Equipment Record
│   ├── View Equipment
│   │   ├── List All Equipment
│   │   ├── Filter by Status
│   │   ├── Filter by Department
│   │   ├── Filter by Location
│   │   ├── Search Equipment
│   │   └── View Equipment Details
│   └── Equipment Operations
│       ├── Generate QR Code
│       ├── Print Equipment Label
│       └── Export Equipment Data
├── User Management
│   ├── Create User Account
│   │   ├── Validate User Data
│   │   ├── Create Admin Account
│   │   ├── Create Technician Account
│   │   └── Create Department Admin Account
│   ├── Edit User
│   │   ├── Update User Information
│   │   ├── Update User Role
│   │   └── Update User Profile
│   ├── Delete User
│   │   └── Remove User Account
│   ├── View Users
│   │   ├── List All Users
│   │   ├── Filter by Role
│   │   └── View User Details
│   └── Account Management
│       ├── View Admin Accounts
│       └── Manage Account Permissions
├── Request Management
│   ├── View Requests
│   │   ├── View Service Requests
│   │   ├── View System Requests
│   │   ├── Filter by Status
│   │   └── View Request Details
│   ├── Approve Request
│   │   ├── Validate Request
│   │   ├── Update Request Status
│   │   ├── Assign to Technician
│   │   └── Generate Approval Notification
│   ├── Reject Request
│   │   ├── Validate Rejection Reason
│   │   ├── Update Request Status
│   │   └── Generate Rejection Notification
│   ├── Update Request
│   │   ├── Modify Request Details
│   │   └── Update Request Status
│   └── Request Operations
│       ├── Generate Request PDF
│       ├── Preview Request
│       └── Delete Request
├── Maintenance Management
│   ├── View Maintenance Records
│   │   ├── List All Maintenance
│   │   ├── Filter by Status
│   │   ├── Filter by Technician
│   │   └── View Maintenance Details
│   ├── Assign Maintenance
│   │   ├── Select Technician
│   │   ├── Set Processing Time
│   │   └── Set Processing Deadline
│   ├── Update Maintenance Status
│   │   ├── Mark as In Progress
│   │   ├── Mark as Completed
│   │   └── Add Maintenance Remarks
│   └── Maintenance Operations
│       ├── View Maintenance History
│       └── Generate Maintenance Report
├── Reports Management
│   ├── Generate Equipment Reports
│   │   ├── Complete Inventory Report
│   │   ├── Incomplete Inventory Report
│   │   └── Department Equipment Report
│   ├── Generate Maintenance Reports
│   │   └── Maintenance Activity Report
│   ├── Generate Financial Reports
│   │   └── Equipment Acquisition Report
│   └── Report Operations
│       ├── Export to PDF
│       ├── Filter by Date Range
│       └── Filter by Department
├── System Logs Management
│   ├── View System Logs
│   │   ├── List All Logs
│   │   ├── Filter by User
│   │   ├── Filter by Action Type
│   │   ├── Filter by Date Range
│   │   └── View Log Details
│   ├── View Activity Logs
│   │   └── Track User Activities
│   └── Log Operations
│       ├── Search Logs
│       └── Export Logs
├── Department Management
│   ├── View Departments
│   │   ├── List All Departments
│   │   ├── View Department Equipment Count
│   │   └── View Department Details
│   └── Department Operations
│       └── Generate Department Report
└── Prevention Maintenance Plan
    ├── Create Maintenance Plan
    │   ├── Define Maintenance Schedule
    │   ├── Assign Equipment
    │   └── Set Maintenance Tasks
    └── View Maintenance Plans
        └── Manage Scheduled Maintenance
```
