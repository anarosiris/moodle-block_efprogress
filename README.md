# Training Evidence: My Progress (`block_efprogress`)

Free course block for Moodle 5.1+ by eLearning Fácil. It shows **each student their own progress** in the course at a
glance, rendered on the server (the only JavaScript is a few lines for the tooltips on touch screens) and built
mobile-first:

- **My Progress (activities)** — a ring with the percentage and "N of M", plus a grid of **marks**, one per activity
  with completion enabled: ✓ done · ● you are here · ○ pending. Every mark is a link to its activity. The list is live:
  activities added or removed during the course appear and disappear on their own. Completion is Moodle's official
  activity completion, whatever the method (view, grade…); the block only reflects it.
- **Completions outside the training period** (when the Training Evidence Report is installed and the course, group or
  enrolment has dates): drawn as a toned-down dashed ✓, counted apart ("N outside the period") and left out of the
  ring, exactly as the report does, so block and report always agree.
- **Restricted activities** that the course page lists greyed out (date, previous completion…) appear as a **padlock**,
  without a link and with the reason in the tooltip, and count towards the total, so "N of M" does not jump as
  activities unlock. Activities restricted to another group, hidden activities and restrictions set to hide entirely
  never appear. Can be turned off per block.
- **Encouragement messages** from 60% and at 100% ("Activities completed!" — never "course completed", because course
  completion may depend on criteria the block does not see). Site-wide default texts, customisable per course, and can
  be turned off per block.
- **Layout**: automatic (adapts to the block width), vertical (side column) or horizontal (wide area above the content).

The block is shown only to users with their own progress in the course (enrolled students). Teachers and managers see
a short hint when the course has no activity with completion enabled.

### Time section (optional)

The block can also show a **My time** section (time spent and, with an expected course duration, a second ring with
the percentage and the time left). This section appears **only if the site has eLearning Fácil's reporting plugin
(`local_efreport`, a commercial plugin) installed and that course measures time with it**. Without it the time section
stays hidden and everything else works as described above. When that plugin is installed, the activities ring also
uses the same figures as its report.

Current version: **0.6.5** — see [CHANGES.md](CHANGES.md).

## Documentation

- User guide (PDF, English): [docs/manual/Guide_block_efprogress_en.pdf](docs/manual/Guide_block_efprogress_en.pdf)
- Guía de uso (PDF, español): [docs/manual/Guia_block_efprogress.pdf](docs/manual/Guia_block_efprogress.pdf)

## Installation

1. **Back up your site first** (database, moodledata and code) — good practice with any plugin.
2. Site administration → Plugins → Install plugins → upload the ZIP and confirm.
3. In a course, turn editing on and add the **My Progress** block.

Requirements: Moodle 5.1, 5.2 or 5.3 · PHP 8.2+ · MySQL/MariaDB or PostgreSQL.

## Configuration

- **Site** (Site administration → Plugins → Blocks → My Progress): the course custom field that holds the
  **expected duration** (accepts hours, "30 h", "90 min" or hh:mm) and the default encouragement texts.
- **Block instance** (course by course): show progress · show restricted activities as locked steps · encouragement
  messages (with course-specific texts) · show time · duration source (course field, manual or none) · layout.

## Permissions and privacy

`block/efprogress:addinstance` lets editing teachers and managers add the block. The block shows only the student's own
data and stores no personal data (null privacy provider).

## Support

Report bugs and ask questions in this repository's [issue tracker](https://github.com/anarosiris/moodle-block_efprogress/issues).

Developed and maintained by [eLearning Fácil](https://elearningfacil.com), an independent company that builds plugins for Moodle™ for training providers and companies. Not affiliated with Moodle Pty Ltd.

## Licence

GNU GPL v3 or later — see [LICENSE](LICENSE). Moodle™ is a registered trademark of Moodle Pty Ltd. eLearning Fácil is
not affiliated with Moodle Pty Ltd.
