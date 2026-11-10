# Changelog — My progress (block_efprogress)

All notable changes to this plugin are listed here.

## [0.6.4] - 2026-11-10

### Documentation
- Guides, last page: Class Manager reads «da de alta a sus estudiantes» / "signs up their students". No code changes.

## [0.6.3] - 2026-11-08

### Changed
- Spanish guide: the install step now uses the real Moodle 5.1–5.3 label «Agregar un bloque» (was «Añadir un bloque»). No code changes.

## [0.6.2] - 2026-10-30

### Changed
- Spanish guide now says «estudiante» instead of «alumno», consistently with the interface. No code changes.

## [0.6.1] - 2026-10-30

### Changed
- Spanish plugin name joins the family: "Seguimiento de la Formación: Mi progreso". English block title "My Progress" (capital P, as a product name) and the same in the capability and settings labels; the hints name the "Training Evidence Report"; native-English review of the language file (23 strings). No change in behaviour.

### Documentation
- English and Spanish guides and README updated to the new names.

## [0.6.0] - 2026-10-28

### Fixed
- Grid and ring could disagree when eLearning Fácil's reporting plugin is installed: an activity completed outside the
  student's training period (course dates, group sheet or enrolment period) was drawn as a plain ✓ while the ring and
  the counter, which follow the report, left it out ("21 marks done, counter says 6"). Those completions are now drawn
  as a toned-down dashed ✓ with the tooltip "done outside your training period (not counted)", the counter line adds
  "N outside the period" and always adds up to the ring's total, and the legend explains the new mark. Nothing changes
  on sites without the reporting plugin.

### Changed
- English plugin name: "Training Evidence: My Progress" (the block title students see is still "My progress").

## [0.5.2] - 2026-10-13

### Changed
- Documentation only: README and changelog rewritten in English; user guide in English and Spanish. No code changes.

## [0.5.1] - 2026-10-09

### Changed
- Supported Moodle versions: 5.1 to 5.3.

### Added
- Automated test: the block is empty for guests, visitors, non-editing teachers and users from other courses.

## [0.5.0] - 2026-10-07

### Fixed
- When eLearning Fácil's reporting plugin is installed, the activities ring shows the same figures as its report
  (same activities and period), and the time section only counts time within the student's period.
- Percentages are rounded down (29 h 59 min 50 s of 30 h is not 100%; 199 of 200 activities is not 100%).
- An activity completed with a failing grade counts as done and is marked in amber ("done, failed").
- Activities inside a restricted subsection appear with a padlock and the subsection's reason (they used to
  disappear); activities in a fully hidden subsection or section do not appear.
- Activity names and encouragement texts are no longer double-escaped.
- The hint for managers when no activity has completion enabled is shown again.
- The manual duration is validated (whole hours, minutes 0–59); dark-mode styles now apply.

## [0.4.3] - 2026-10-03

### Changed
- Documentation only: updated user guide.

## [0.4.2] - 2026-09-30

### Fixed
- Activities restricted to another group (or in a section restricted to another group) no longer appear with a padlock
  nor count towards the total, so every student can reach 100%. Courses without group restrictions are unaffected.

## [0.4.1] - 2026-08-11

### Added
- Activities in **restricted sections** that the course page lists greyed out appear locked, with the section's
  reason. Activities with a visible restriction of their own show their own reason.

## [0.4.0] - 2026-08-11

### Added
- **Restricted activities** (date, previous completion…) that the course page lists greyed out appear as a padlock,
  without a link and with the reason in the tooltip (also on touch screens). They count towards the total so "N of M"
  does not jump as activities unlock; the counter adds "X locked".
- Block setting "Show restricted activities as locked steps" (on by default).

## [0.3.3] - 2026-07-26

### Changed
- Visible names no longer carry third-party trademarks.

## [0.3.2] - 2026-07-26

### Changed
- Code comments in English; README in English; licence file added.
- Requires Moodle 5.1 or later; tested on MySQL/MariaDB and PostgreSQL.

## [0.3.1] - 2026-07-12

### Fixed
- Accessibility (WCAG AA): correct list roles for the marks grid.

## [0.3.0] - 2026-07-09

### Added
- Layout setting per block: automatic, vertical or horizontal. Horizontal places the rings side by side and the marks
  across the full width; automatic picks one from the real block width (CSS container query) and falls back to
  vertical in older browsers.

## [0.2.4] - 2026-07-07

### Changed
- Updated user guide with screenshots.

## [0.2.3] - 2026-07-07

### Changed
- Coding style clean-up (no behaviour changes).

## [0.2.2] - 2026-07-07

### Added
- File integrity check (Site administration → Reports → System status): lists any plugin file missing, empty or
  corrupted after installation.

## [0.2.1] - 2026-07-06

### Fixed
- The block is shown only to users with their own progress in the course (enrolled students); managers and read-only
  roles no longer see an empty "My progress".

## [0.2.0] - 2026-07-05

### Changed
- The "My time" section comes first.
- Smaller marks on desktop; touch targets stay at 40 px or more.
- On touch devices, the first tap on a mark shows the activity name and status; the second opens it.

## [0.1.0] - 2026-07-04

### Added
- First release: "My progress" ring and clickable marks grid for activities with completion enabled; optional
  "My time" section; encouragement messages at 60% and 100%; accessible and mobile-first; null privacy provider;
  English and Spanish strings; PHPUnit tests.
