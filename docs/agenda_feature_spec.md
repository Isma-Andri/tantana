# Agenda Feature Specification

## Overview
Transform **Tantana** into an agenda manager for heads of state and international organisations. Projects become *policy dossiers* (e.g., accords, resolutions) that centralise deadlines, documents, minutes and enable secure sharing between ministries and partners.

## Core Additions
- ► **Policy Dossier Model** – extends the existing `Project` schema with fields: `type`, `status`, `deadline`, `participants`.
- ► **Attachment Model** – stores metadata for PDF, PPT, DOCX files linked to a dossier.
- ► **Workflow Model** – tracks stages `draft → review → signed` with timestamps and reviewer comments.
- ► **Access Control** – role‑based permissions (Ministry, Partner, Administrator) enforced at API and UI layers.

## Backend Changes (Node/Express)
- **Models** (`models/`)
  - `policyDossier.js` – Sequelize definition with relations to `Attachment` and `Workflow`.
  - `attachment.js` – file‑storage handling (S3‑compatible or local storage).
  - `workflow.js` – state machine logic.
- **Controllers** (`controllers/`)
  - `dossierController.js` – CRUD endpoints, attachment upload, workflow transitions.
  - `attachmentController.js` – secure download with token.
  - `workflowController.js` – approve/reject actions.
- **Routes** (`routes/`)
  - Register new REST routes under `/api/dossiers`.
- **Migrations** (`migrations/`)
  - Add tables `policy_dossiers`, `attachments`, `workflows`.

## Front‑end Changes (Vue/React)
- **Components**
  - `DossierList`, `DossierDetail`, `AttachmentUploader`, `WorkflowStepper`.
- **Views** (`views/`)
  - New pages: `/dossiers`, `/dossiers/:id`.
- **State Management**
  - Store dossier data, attachment list, workflow state.
- **PDF Export**
  - Server‑side generation using `pdfkit` (or similar) exposing `/api/dossiers/:id/export`.

## Security Enhancements
- JWT claims include `role` and `organisationId`.
- Middleware checks for required role on each dossier/attachment endpoint.
- Files stored outside web root; downloads served via signed URLs.

## Development Calendar (2 weeks)
| Day | Objectives |
|-----|------------|
| **Week 1 – Architecture & Backend** |
| Mon | Design database schema, create migration scripts. |
| Tue | Implement Sequelize models (`policyDossier`, `attachment`, `workflow`). |
| Wed | Build controller skeletons and route registration. |
| Thu | Implement attachment upload (multipart) and secure download. |
| Fri | Add workflow state machine and unit tests. |
| **Week 2 – Front‑end & Integration** |
| Mon | Create dossier list and detail UI components. |
| Tue | Implement attachment uploader UI, integrate with backend API. |
| Wed | Build workflow stepper component, wire up approve/reject actions. |
| Thu | Add PDF export endpoint and UI button. |
| Fri | End‑to‑end testing, security review, polish UI, prepare MVP demo. |

The stack (Node/Express, PostgreSQL, Vue/React) remains unchanged; new code builds on existing patterns. AI agents can be tasked with scaffolding files, writing boiler‑plate, and generating tests to accelerate the MVP delivery.
