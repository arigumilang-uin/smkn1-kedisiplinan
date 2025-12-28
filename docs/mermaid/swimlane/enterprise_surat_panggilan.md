# Enterprise Swimlane Activity Diagram: Kelola Surat Panggilan

```mermaid
flowchart TD
    %% =================================================================================
    %% GLOBAL STYLES
    %% =================================================================================
    classDef activity fill:#F5F7F8,stroke:#B0BEC5,stroke-width:1px,color:#263238,rx:5,ry:5;
    classDef processing fill:#E3F2FD,stroke:#90CAF9,stroke-width:1px,color:#1565C0,rx:5,ry:5;
    classDef decision fill:#ECEFF1,stroke:#90A4AE,stroke-width:1px,color:#263238;
    classDef terminator fill:#37474F,stroke:#263238,stroke-width:2px,color:#FFFFFF,rx:50,ry:50;
    classDef lane fill:#FFFFFF,stroke:#CFD8DC,stroke-width:2px,color:#455A64,font-weight:bold;

    %% =================================================================================
    %% SWIMLANE: PEMBINA
    %% =================================================================================
    subgraph PEMBINA ["👤 PEMBINA"]
        direction TB
        START([Start]):::terminator
        A[Buka Detail Kasus]:::activity
        B[Klik Tombol 'Lihat Surat']:::activity
        C[Review Preview Surat]:::activity
        D{Perlu Edit Data?}:::decision
        E[Edit Konten Surat]:::activity
        F[Simpan Perubahan]:::activity
        G[Klik 'Cetak Surat']:::activity
        H[Review Hasil PDF]:::activity
        I{Cetak ke Printer?}:::decision
        J[Print Dokumen Fisik]:::activity
        K[Simpan File PDF]:::activity
        END([End]):::terminator
    end

    %% =================================================================================
    %% SWIMLANE: SISTEM
    %% =================================================================================
    subgraph SISTEM ["⚙️ SISTEM"]
        direction TB
        L[Render Template HTML]:::processing
        M[Validasi & Update Data]:::processing
        N[Generate PDF (DomPDF)]:::processing
        O[Catat Log Pencetakan]:::activity
    end

    %% =================================================================================
    %% ALIGNMENT TWEAKS
    %% =================================================================================
    A ~~~ L
    B ~~~ L

    %% =================================================================================
    %% FLOW LOGIC
    %% =================================================================================
    %% 1. View
    START --> A
    A --> B
    B --> L
    L --> C

    %% 2. Edit Loop
    C --> D
    D -- Ya --> E
    E --> F
    F --> M
    M --> C

    %% 3. Print Flow
    D -- Tidak (Sudah OK) --> G
    G --> N
    N --> O
    O --> H

    %% 4. Final Action
    H --> I
    I -- Ya --> J
    I -- Tidak --> K

    J --> END
    K --> END

    %% =================================================================================
    %% STYLE APPLICATION
    %% =================================================================================
    class PEMBINA,SISTEM lane;
    linkStyle default stroke:#546E7A,stroke-width:1px,fill:none;
```
