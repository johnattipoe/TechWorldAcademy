# TechWorld Academy assets

These assets use the blue, navy and orange graduation-cap identity shown in the supplied reference.

- `techworld-mark.svg`: compact academy mark used in the website, learning platform, loader and favicon touch icon.
- `techworld-logo.svg`: full wordmark and tagline for documents, promotional material and light backgrounds.
- `favicon.svg`: browser tab icon.
- `techworld-app-icon.png` and `apple-touch-icon.png`: 512px install/app artwork and 180px touch icon, cropped from the supplied reference.
- `techworld-icons.svg`: reusable SVG symbol sheet. Available symbols: `graduation`, `web-development`, `mobile`, `analytics`, `cybersecurity`, `design`, `database`, `cloud`, `ai`, `business`, `video-learning`, `certificate`, `support`, `python`, `html`, `javascript`, `java`, `php`, `code-cpp`, `globe`, `checklist`, `book-open`, `lesson`, `quiz`, `progress`, `instructor`, `community`, `accessibility`, and `calendar`.

In PHP templates, use `tech_icon('cloud')` or `tech_icon(course_icon_key($courseTitle), 'learning-course-icon')`. The helper restricts symbol names to this sheet and emits decorative, hidden-from-screen-reader SVG markup.

## Image assets

The four PNG scenes in `../images/` are crops from the reference image supplied for this project: student learning, an online course interface, instructor work and learner collaboration. They are used in image galleries on both the public website and learning catalogue. Their source resolution follows the supplied reference.

The scalable course illustrations in `../images/course-art/` cover web development, data analytics, cybersecurity, cloud and AI, mobile design, and programming. They are used on course cards throughout the learning platform.
