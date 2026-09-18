# SparkleStation  
### Personal HSR Data Hub

**Version: 1.0.1**

SparkleStation is a **Laravel-based personal hub** for exploring and managing **Honkai: Star Rail** data. It focuses on data presentation, UI experimentation and interactive systems.

The project was created as a learning exercise and portfolio piece.

---

## Overview

This application provides:
- Structured access to **characters, Light Cones, patches, relics, and other game data**
- Multiple view modes with sorting and filtering options
- A gacha system for simulating character and Light Cone pulls
- A relic system for generating, leveling, and managing relics
- A character management system for equipping Light Cones and relics
- A mix of **MPA and SPA approaches**, depending on each feature’s requirements

The project serves both as a **personal exploration space** and a **technical showcase**.

To keep the repository size manageable, the full library of game assets (~550 MB of images) and the production database are not included in this repo.

---

## Tech Stack

- **Backend:** Laravel
- **Database:** MySQL using Eloquent ORM for structured data relationships
- **Frontend:** Blade, Vanilla JavaScript, Vue.js (selectively)  
- **Architecture:** Server-rendered pages with targeted SPA components  
- **Data Handling:** AJAX-driven modals, dynamic filtering, client-side state  
- **Styling:** Tailwind CSS with project-specific custom styles

I intentionally limited Vue to the User Panel to see how far I could push Vanilla JS in the Relics section before the complexity became unmanageable.

---

## Homepage

![Homepage](docs/homepage.png)

The homepage acts as a live dashboard:
- Current patch information
- Active redemption codes
- Site-wide statistics
- Random character spotlight (refreshed on each reload)

---

## Characters

https://github.com/user-attachments/assets/c93a2493-1662-4e29-bbe1-1b1b34bbda70

The character section supports two distinct presentation modes:
- **Icon grid view** for fast browsing
- **Table view** for an alternative structured layout

Features include:
- Filtering by element, path, faction, etc.
- Sorting (ascending / descending / none)
- Click-to-sort columns in table mode

All filtering and sorting logic is handled client-side for responsiveness.

---

## Patches

https://github.com/user-attachments/assets/e5e4d02d-be52-426f-b250-838d4e753997

Patch data can be explored through:
- **Timeline view** for chronological context
- **List view** for quick navigation

In timeline mode:
- Selecting a patch triggers an AJAX request
- A loading state is displayed
- Patch details are shown in a modal  
  (characters, light cones)

---

## Relics

https://github.com/user-attachments/assets/20a27313-135d-421d-8093-8cd185db077c

The Relics section is built as a **Vanilla JavaScript SPA**. I created it without a frontend framework to practice manual state management, DOM updates and event handling.

---

## Gacha System

https://github.com/user-attachments/assets/381d7c83-78ee-4fbe-80d0-65e543f9d216

The gacha system simulates character and Light Cone banners. Obtained items are added to the user's account and can later be managed through the User Panel.

## User Panel

The user panel is the **only Vue-powered section** of the application.

### Inventory and Settings

https://github.com/user-attachments/assets/f6d38114-072b-4f29-bd8f-f4f683967ed7

Features:
- User settings
- Personal relic inventory
- Relic generation and leveling system

### Character Management

https://github.com/user-attachments/assets/83bc4b9d-bc53-4cd8-b596-ea6b6f05ce83

Users can:
- Manage their characters obtained from the gacha system
- Equip and enhance Light Cones
- Assign relics
- Inspect calculated stats
- View and activate eidolons

I used Vue for the User Panel because its interconnected inventory and character-management features required more complex reactive state.

---

## Learning Goals

- Practice structuring a Laravel application
- Experiment with different UI approaches for the same data
- Learn when to use Vanilla JavaScript vs Vue.js
- Improve handling of client-side state and AJAX interactions
- Keep the codebase understandable and easy to extend

---

## Local Development Note
This repository is intended as a **code showcase** rather than a plug-and-play application. 
- **Database:** The core data is private; however, the schema can be reviewed in the `/database/migrations` directory.
- **Assets:** Game assets are excluded due to copyright and size.

---

## Legal & Credits
**SparkleStation** is a fan-made project and is not affiliated with or endorsed by **HoYoverse**.

- **Assets:** All game assets, including images, icons, and character data, are the property of **© HoYoverse (Cognosphere)**.
- **Project Purpose:** This is a non-commercial project created for educational and portfolio purposes. No copyright infringement is intended.
