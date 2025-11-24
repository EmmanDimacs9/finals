# Functional Decomposition Diagram (FDD) - DEPARTMENT MODULE

```
Department System
├── Dashboard Management
│   ├── View Statistics
│   │   ├── Total Requests Count
│   │   ├── Pending Requests Count
│   │   ├── Completed Requests Count
│   │   └── Activity Logs Count
│   ├── View Request Analytics
│   │   ├── Request Distribution by Type
│   │   │   ├── Preventive Maintenance Plan
│   │   │   ├── ICT Service Request Form
│   │   │   ├── System Request
│   │   │   ├── Posting Request
│   │   │   ├── Preventive Maintenance Plan Index Card
│   │   │   ├── Website Posting
│   │   │   ├── User Account Request
│   │   │   ├── Website Posting Request
│   │   │   ├── Announcement Request
│   │   │   ├── Request for Posting of Announcements / Greetings
│   │   │   └── ISP Evaluation
│   │   ├── Request Status Summary
│   │   ├── Approved Requests Count
│   │   ├── Pending Requests Count
│   │   └── Rejected Requests Count
│   └── Dashboard Operations
│       ├── Filter by Date Range
│       └── View Request Trends
├── Service Request Management
│   ├── Submit Service Request
│   │   ├── ICT Service Request Form
│   │   ├── Validate Request Data
│   │   ├── Submit Request
│   │   └── Generate Request PDF
│   ├── View Service Requests
│   │   ├── List All Service Requests
│   │   ├── Filter by Status
│   │   ├── View Request Details
│   │   └── View Request Status
│   └── Service Request Operations
│       ├── Track Request Status
│       ├── View Request History
│       └── Generate Request Report
├── System Request Management
│   ├── Submit System Request
│   │   ├── System Request Form
│   │   ├── Validate Request Data
│   │   ├── Submit Request
│   │   └── Generate Request PDF
│   ├── View System Requests
│   │   ├── List All System Requests
│   │   ├── Filter by Status
│   │   ├── View Request Details
│   │   └── View Request Status
│   └── System Request Operations
│       ├── Track Request Status
│       ├── View Request History
│       └── Generate System Request Report
├── Posting Request Management
│   ├── Submit Posting Request
│   │   ├── Posting Request Form
│   │   ├── Validate Request Data
│   │   ├── Submit Request
│   │   └── Generate Request PDF
│   ├── View Posting Requests
│   │   ├── List All Posting Requests
│   │   ├── Filter by Status
│   │   ├── View Request Details
│   │   └── View Request Status
│   └── Posting Request Operations
│       ├── Track Request Status
│       ├── View Request History
│       └── Generate Posting Request Report
├── Website Posting Request Management
│   ├── Submit Website Posting Request
│   │   ├── Website Posting Request Form
│   │   ├── Validate Request Data
│   │   ├── Submit Request
│   │   └── Generate Request PDF
│   ├── View Website Posting Requests
│   │   ├── List All Website Posting Requests
│   │   ├── Filter by Status
│   │   ├── View Request Details
│   │   └── View Request Status
│   └── Website Posting Request Operations
│       ├── Track Request Status
│       ├── View Request History
│       └── Generate Website Posting Request Report
├── Announcement Request Management
│   ├── Submit Announcement Request
│   │   ├── Announcement Request Form
│   │   ├── Validate Request Data
│   │   ├── Submit Request
│   │   └── Generate Request PDF
│   ├── View Announcement Requests
│   │   ├── List All Announcement Requests
│   │   ├── Filter by Status
│   │   ├── View Request Details
│   │   └── View Request Status
│   └── Announcement Request Operations
│       ├── Track Request Status
│       ├── View Request History
│       └── Generate Announcement Request Report
├── Posting Announcements/Greetings Request Management
│   ├── Submit Posting Announcements/Greetings Request
│   │   ├── Request for Posting of Announcements / Greetings Form
│   │   ├── Validate Request Data
│   │   ├── Submit Request
│   │   └── Generate Request PDF
│   ├── View Posting Announcements/Greetings Requests
│   │   ├── List All Posting Announcements/Greetings Requests
│   │   ├── Filter by Status
│   │   ├── View Request Details
│   │   └── View Request Status
│   └── Posting Announcements/Greetings Request Operations
│       ├── Track Request Status
│       ├── View Request History
│       └── Generate Posting Announcements/Greetings Request Report
├── User Account Request Management
│   ├── Submit User Account Request
│   │   ├── User Account Request Form
│   │   ├── Validate Request Data
│   │   ├── Submit Request
│   │   └── Generate Request PDF
│   ├── View User Account Requests
│   │   ├── List All User Account Requests
│   │   ├── Filter by Status
│   │   ├── View Request Details
│   │   └── View Request Status
│   └── User Account Request Operations
│       ├── Track Request Status
│       ├── View Request History
│       └── Generate User Account Request Report
├── ISP Evaluation Management
│   ├── Submit ISP Evaluation Request
│   │   ├── ISP Evaluation Form
│   │   ├── Validate Request Data
│   │   ├── Submit Request
│   │   └── Generate Request PDF
│   ├── View ISP Evaluation Requests
│   │   ├── List All ISP Evaluation Requests
│   │   ├── Filter by Status
│   │   ├── View Request Details
│   │   └── View Request Status
│   └── ISP Evaluation Request Operations
│       ├── Track Request Status
│       ├── View Request History
│       └── Generate ISP Evaluation Request Report
├── Preventive Maintenance Plan Management
│   ├── Submit Preventive Maintenance Plan
│   │   ├── Preventive Maintenance Plan Form
│   │   ├── Define Maintenance Schedule
│   │   ├── Select Equipment
│   │   ├── Set Maintenance Tasks
│   │   ├── Set Maintenance Frequency
│   │   ├── Validate Request Data
│   │   ├── Submit Request
│   │   └── Generate Request PDF
│   ├── View Preventive Maintenance Plans
│   │   ├── List All Preventive Maintenance Plans
│   │   ├── Filter by Status
│   │   ├── View Plan Details
│   │   ├── View Scheduled Maintenance
│   │   ├── View Maintenance History
│   │   └── View Request Status
│   └── Preventive Maintenance Plan Operations
│       ├── Track Request Status
│       ├── View Request History
│       └── Generate Preventive Maintenance Plan Report
├── Preventive Maintenance Plan Index Card Management
│   ├── Submit Preventive Maintenance Plan Index Card
│   │   ├── Preventive Maintenance Plan Index Card Form
│   │   ├── Validate Request Data
│   │   ├── Submit Request
│   │   └── Generate Request PDF
│   ├── View Preventive Maintenance Plan Index Cards
│   │   ├── List All Preventive Maintenance Plan Index Cards
│   │   ├── Filter by Status
│   │   ├── View Request Details
│   │   └── View Request Status
│   └── Preventive Maintenance Plan Index Card Operations
│       ├── Track Request Status
│       ├── View Request History
│       └── Generate Preventive Maintenance Plan Index Card Report
├── Checklist Management
│   ├── Create Checklist
│   │   ├── Define Checklist Items
│   │   ├── Set Checklist Categories
│   │   └── Save Checklist
│   ├── View Checklist
│   │   ├── List All Checklists
│   │   ├── View Checklist Details
│   │   └── View Checklist Items
│   ├── Update Checklist
│   │   ├── Add Checklist Items
│   │   ├── Remove Checklist Items
│   │   └── Update Checklist Categories
│   └── Checklist Operations
│       ├── Generate Checklist PDF
│       └── Submit Checklist Survey
├── Remarks Management
│   ├── Submit Remarks
│   │   ├── Add Equipment Remarks
│   │   ├── Add Maintenance Remarks
│   │   └── Submit Remarks
│   ├── View Remarks
│   │   ├── List All Remarks
│   │   ├── Filter by Equipment
│   │   ├── Filter by Date
│   │   └── View Remarks Details
│   └── Remarks Operations
│       └── Update Remarks
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
│   │   ├── View Department Information
│   │   └── View Account Details
│   ├── Update Profile
│   │   ├── Update Personal Information
│   │   ├── Update Profile Image
│   │   ├── Update Department Information
│   │   └── Change Password
│   └── Profile Operations
│       └── View Profile Statistics
```
