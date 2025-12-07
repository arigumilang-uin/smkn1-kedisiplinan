# Guide: Membuat Use Case Diagram di draw.io

## Quick Start

### Option 1: Manual Creation (Recommended)
1. Buka https://app.diagrams.net/
2. Create New Diagram → UML → Use Case Diagram
3. Follow struktur di bawah

### Option 2: Import Template
Saya tidak bisa generate XML langsung, tapi saya berikan struktur lengkap untuk dibuat manual.

---

## Struktur Diagram

### Layout:
```
┌────────────────────────────────────────────────────────────────────┐
│                                                                    │
│  [Actors]          [System Boundary]          [External Systems]  │
│   (Left)                (Center)                    (Right)        │
│                                                                    │
└────────────────────────────────────────────────────────────────────┘
```

---

## Step-by-Step Creation

### STEP 1: Setup Canvas
1. Open draw.io
2. File → New → Blank Diagram
3. Set page size: A3 Landscape
4. Enable Grid: View → Grid

### STEP 2: Add Actors (Left Side)

**Position**: X=50, Y=100, spacing=120px

1. **Operator Sekolah** (Y=100)
   - Shape: Actor (stick figure)
   - Label: "Operator Sekolah"
   - Color: Blue

2. **Kepala Sekolah** (Y=220)
   - Shape: Actor
   - Label: "Kepala Sekolah"
   - Color: Red

3. **Waka Kesiswaan** (Y=340)
   - Shape: Actor
   - Label: "Waka Kesiswaan"
   - Color: Green

4. **Kaprodi** (Y=460)
   - Shape: Actor
   - Label: "Kaprodi"
   - Color: Orange

5. **Wali Kelas** (Y=580)
   - Shape: Actor
   - Label: "Wali Kelas"
   - Color: Purple

6. **Guru** (Y=700)
   - Shape: Actor
   - Label: "Guru"
   - Color: Brown

7. **Wali Murid** (Y=820)
   - Shape: Actor
   - Label: "Wali Murid"
   - Color: Pink

8. **Waka Sarana** (Y=940)
   - Shape: Actor
   - Label: "Waka Sarana"
   - Color: Teal

9. **Developer** (Y=1060)
   - Shape: Actor
   - Label: "Developer"
   - Color: Gray

### STEP 3: Create System Boundary

**Position**: X=200, Y=50, Width=800, Height=1100

1. Draw Rectangle
2. Label: "SIMDIS - Sistem Kedisiplinan"
3. Style: Dashed border, light gray fill
4. Font: Bold, 16pt

### STEP 4: Add Use Cases (Inside Boundary)

**Format**: Oval shape, centered text

#### Module 1: Authentication (Y=100)
```
X=300, Y=100
○ Login
○ Logout  
○ Manage Profile
```

#### Module 2: Master Data (Y=250)
```
X=300, Y=250
○ Manage Users
○ Manage Siswa
○ Manage Jurusan
○ Manage Kelas
○ Manage Jenis Pelanggaran
```

#### Module 3: Rules Engine (Y=450)
```
X=300, Y=450
○ Configure Frequency Rules
○ Configure Accumulation Rules
○ Configure Pembinaan Internal Rules
```

#### Module 4: Pelanggaran (Y=600)
```
X=600, Y=600
○ Catat Pelanggaran
○ View Riwayat Pelanggaran
○ Edit Riwayat Pelanggaran
○ Delete Riwayat Pelanggaran
```

#### Module 5: Tindak Lanjut (Y=800)
```
X=600, Y=800
○ View Tindak Lanjut
○ Update Status Tindak Lanjut
○ Approve/Reject Tindak Lanjut
```

#### Module 6: Reporting (Y=950)
```
X=300, Y=950
○ View Dashboard
○ Generate Report
○ View Siswa Perlu Pembinaan
```

#### Module 7: Notification (Y=1050)
```
X=600, Y=1050
○ Receive Notification
○ View Notification List
```

#### Module 8: Audit (Y=1150)
```
X=300, Y=1150
○ View Activity Log
○ View Last Login
○ View Account Status
```

### STEP 5: Add External Systems (Right Side)

**Position**: X=1100, Y=400, spacing=150px

1. **Email Server** (Y=400)
   - Shape: Actor (or Component)
   - Label: "<<External>>\nEmail Server"
   - Color: Light Blue

2. **File Storage** (Y=550)
   - Shape: Actor (or Component)
   - Label: "<<External>>\nFile Storage"
   - Color: Light Green

3. **PDF Generator** (Y=700)
   - Shape: Actor (or Component)
   - Label: "<<External>>\nPDF Generator"
   - Color: Light Orange

### STEP 6: Draw Associations

**Line Style**: Solid line, no arrow

#### Operator Sekolah → Use Cases:
- Login
- Logout
- Manage Profile
- Manage Users
- Manage Siswa
- Manage Jurusan
- Manage Kelas
- Manage Jenis Pelanggaran
- Configure Frequency Rules
- Configure Accumulation Rules
- Configure Pembinaan Internal Rules
- Catat Pelanggaran
- View Riwayat Pelanggaran
- Edit Riwayat Pelanggaran
- Delete Riwayat Pelanggaran
- View Tindak Lanjut
- View Dashboard
- Generate Report
- View Activity Log
- View Last Login
- View Account Status

#### Kepala Sekolah → Use Cases:
- Login
- Logout
- Manage Profile
- View Riwayat Pelanggaran
- View Tindak Lanjut
- Approve/Reject Tindak Lanjut
- View Dashboard
- Generate Report
- View Siswa Perlu Pembinaan
- Receive Notification
- View Notification List

#### Waka Kesiswaan → Use Cases:
- Login
- Logout
- Manage Profile
- Manage Siswa
- Catat Pelanggaran
- View Riwayat Pelanggaran
- Edit Riwayat Pelanggaran
- Delete Riwayat Pelanggaran
- View Tindak Lanjut
- Update Status Tindak Lanjut
- View Dashboard
- Generate Report

#### Kaprodi → Use Cases:
- Login
- Logout
- Manage Profile
- Catat Pelanggaran
- View Riwayat Pelanggaran
- View Tindak Lanjut
- Update Status Tindak Lanjut
- View Dashboard

#### Wali Kelas → Use Cases:
- Login
- Logout
- Manage Profile
- Manage Siswa (Update Kontak only)
- Catat Pelanggaran
- View Riwayat Pelanggaran
- View Tindak Lanjut
- Update Status Tindak Lanjut
- View Dashboard

#### Guru → Use Cases:
- Login
- Logout
- Manage Profile
- Catat Pelanggaran
- View Riwayat Pelanggaran

#### Wali Murid → Use Cases:
- Login
- Logout
- Manage Profile
- View Dashboard (own child only)

#### Waka Sarana → Use Cases:
- Login
- Logout
- Manage Profile
- Catat Pelanggaran
- View Dashboard

#### Developer → Use Cases:
- All Use Cases (draw line to system boundary)

### STEP 7: Draw Include Relationships

**Line Style**: Dashed arrow, label "<<include>>"

1. **Catat Pelanggaran** --<<include>>--> **Trigger Rules Engine**
2. **Trigger Rules Engine** --<<include>>--> **Create Tindak Lanjut**
3. **Create Tindak Lanjut** --<<include>>--> **Send Notification**
4. **Login** --<<include>>--> **Check Account Active**
5. **All CRUD** --<<include>>--> **Log Activity**

### STEP 8: Draw Extend Relationships

**Line Style**: Dashed arrow, label "<<extend>>"

1. **Preview Impact** --<<extend>>--> **Catat Pelanggaran**
2. **Filter by Criteria** --<<extend>>--> **View Riwayat**
3. **Filter by Criteria** --<<extend>>--> **View Tindak Lanjut**

### STEP 9: Connect External Systems

**Line Style**: Dashed line

1. **Send Notification** -----> **Email Server**
2. **Upload Bukti Foto** -----> **File Storage**
3. **Generate Report** -----> **PDF Generator**

### STEP 10: Add Module Labels

Add text boxes to group use cases:

1. **"Authentication & Authorization"** (above Login group)
2. **"Master Data Management"** (above Manage Users group)
3. **"Rules Engine Configuration"** (above Configure Rules group)
4. **"Pelanggaran Management"** (above Catat Pelanggaran group)
5. **"Tindak Lanjut Management"** (above View Tindak Lanjut group)
6. **"Reporting & Monitoring"** (above View Dashboard group)
7. **"Notification System"** (above Receive Notification group)
8. **"Audit & Activity Log"** (above View Activity Log group)

### STEP 11: Add Legend

**Position**: Bottom right corner

```
┌─────────────────────────┐
│        LEGEND           │
├─────────────────────────┤
│ ○ Use Case              │
│ ─── Association         │
│ ---> <<include>>        │
│ ---> <<extend>>         │
│ 👤 Actor                │
│ ⬜ System Boundary      │
└─────────────────────────┘
```

### STEP 12: Add Title & Metadata

**Position**: Top center

```
┌─────────────────────────────────────────────┐
│  SIMDIS - Use Case Diagram                  │
│  Sistem Kedisiplinan SMKN 1 Siak            │
│                                             │
│  Version: 1.0                               │
│  Date: December 2025                        │
│  Author: Development Team                   │
└─────────────────────────────────────────────┘
```

---

## Color Scheme

### Actors:
- **Operator Sekolah**: #0066CC (Blue)
- **Kepala Sekolah**: #CC0000 (Red)
- **Waka Kesiswaan**: #00CC66 (Green)
- **Kaprodi**: #FF9900 (Orange)
- **Wali Kelas**: #9933CC (Purple)
- **Guru**: #996633 (Brown)
- **Wali Murid**: #FF66CC (Pink)
- **Waka Sarana**: #00CCCC (Teal)
- **Developer**: #666666 (Gray)

### Use Cases:
- **Authentication**: #E6F2FF (Light Blue)
- **Master Data**: #E6FFE6 (Light Green)
- **Rules Engine**: #FFF2E6 (Light Orange)
- **Pelanggaran**: #FFE6E6 (Light Red)
- **Tindak Lanjut**: #F2E6FF (Light Purple)
- **Reporting**: #FFFFE6 (Light Yellow)
- **Notification**: #E6FFFF (Light Cyan)
- **Audit**: #F2F2F2 (Light Gray)

### System Boundary:
- Border: #333333 (Dark Gray), Dashed
- Fill: #F9F9F9 (Very Light Gray)

---

## Tips for Better Diagram

### 1. Alignment:
- Use draw.io's alignment tools
- Arrange → Align → Distribute Horizontally/Vertically

### 2. Spacing:
- Keep consistent spacing between elements
- Use grid snap (View → Grid → Snap to Grid)

### 3. Grouping:
- Group related use cases
- Use containers or colored backgrounds

### 4. Labels:
- Use clear, concise labels
- Avoid abbreviations
- Use consistent naming

### 5. Lines:
- Avoid crossing lines when possible
- Use waypoints to route lines cleanly
- Keep lines as straight as possible

### 6. Size:
- Keep use case ovals consistent size
- Actors should be same size
- Use case text should fit comfortably

---

## Export Options

### For Documentation:
- **PNG**: High resolution (300 DPI)
- **PDF**: Vector format, scalable
- **SVG**: Web-friendly, scalable

### For Presentation:
- **PNG**: Transparent background
- **PDF**: Print-ready

### For Collaboration:
- **draw.io XML**: Editable format
- **Share Link**: Online collaboration

---

## Validation Checklist

Before finalizing:

- [ ] All actors are connected to relevant use cases
- [ ] All use cases are inside system boundary
- [ ] Include/Extend relationships are correct
- [ ] External systems are properly connected
- [ ] Labels are clear and readable
- [ ] Colors are consistent
- [ ] Layout is balanced
- [ ] No overlapping elements
- [ ] Legend is present
- [ ] Title and metadata are added

---

## Alternative: Simplified Version

If diagram is too complex, create multiple diagrams:

### Diagram 1: High-Level Overview
- Show only main modules
- Group use cases into packages
- Show only primary actors

### Diagram 2: Authentication & Master Data
- Detail authentication flow
- Detail master data CRUD

### Diagram 3: Pelanggaran & Tindak Lanjut
- Detail pelanggaran workflow
- Detail approval process

### Diagram 4: Reporting & Monitoring
- Detail reporting features
- Detail dashboard views

---

## Estimated Time

- **Simple Version** (main use cases only): 30 minutes
- **Complete Version** (all details): 2-3 hours
- **Professional Version** (with styling): 4-5 hours

---

## Resources

- **draw.io**: https://app.diagrams.net/
- **UML Tutorial**: https://www.visual-paradigm.com/guide/uml-unified-modeling-language/what-is-use-case-diagram/
- **Best Practices**: https://creately.com/blog/diagrams/use-case-diagram-tutorial/

---

## Summary

Saya sudah provide:
1. ✅ Complete use case list (35+ use cases)
2. ✅ Detailed actor descriptions (9 actors)
3. ✅ Step-by-step creation guide
4. ✅ Layout and positioning
5. ✅ Color scheme
6. ✅ Relationships (include, extend, association)
7. ✅ Tips and best practices

Kamu tinggal follow guide ini di draw.io untuk create diagram yang professional! 🎨
