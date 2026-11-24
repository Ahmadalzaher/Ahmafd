import { mkdir, writeFile } from "node:fs/promises";
import path from "node:path";

const BASE = "https://arab-kulturfestival.de/wp-content/uploads/";
const OUT = "D:/oben code/_assets";
await mkdir(OUT, { recursive: true });

const files = {
  "logo-wordmark.png": "2024/04/Logo.png",
  "logo-mark.png": "2022/09/cropped-Asset-12@4x-8.png",
  "favicon.png": "2022/12/cropped-favicon.png",
  "hero-dabke.jpg": "2025/12/1280x960.jpg",
  "dabke-circle.jpg": "2026/10/1280x960-4-1024x682-1.jpg",
  "kalligrafie.jpg": "2026/10/sriimg20081107_9942914_1-data.jpg",
  "arabic-music.jpg": "2026/10/thumb994.jpg",
  "festival-1.jpg": "2026/10/252.jpg",
  "festival-2.jpg": "2026/10/455214.jpg",
  "festival-3.jpg": "2026/10/55555.jpg",
  "festival-4.jpg": "2026/10/123456.jpg",
  "partner-bkm.png": "2026/10/behoerde-fur-kultur-und-medien-hamburg-logo-bkm-e1612337405177.png",
  "partner-goldbekhaus.png": "2026/10/logo_www_rot-Kopie-1024x966-1.png",
  "partner-arap-library.png": "2025/04/مكتبة-آراب.png",
  "partner-adwaa.png": "2025/04/اضواء.png",
  "partner-rooh-alsharq.png": "2025/04/فرقة-روح-الشرق.png",
  "partner-arap.png": "2023/09/آراب.png",
  "about-team.jpg": "2023/09/FB_IMG_15520926292741.jpg",
  "atmosphere-1.jpg": "2022/12/bg-09-free-img.jpeg",
  "atmosphere-2.jpg": "2022/12/bg-08-free-img.jpeg",
  "atmosphere-3.jpg": "2022/12/bg-07-free-img.jpeg",
  "gallery-1.jpg": "2022/12/gallery-01-free-img.jpeg",
  "gallery-2.jpg": "2022/12/gallery-04-free-img.jpeg",
  "program-2024.pdf": "2024/06/Arab-Kulturfestival-2024.pdf",
  "program-2022.pdf": "2024/06/Arab-Kulturfestival-2022.pdf",
};

const results = [];
for (const [name, rel] of Object.entries(files)) {
  const url = BASE + rel.split("/").map(encodeURIComponent).join("/");
  try {
    const res = await fetch(url);
    if (!res.ok) throw new Error(String(res.status));
    const buf = Buffer.from(await res.arrayBuffer());
    await writeFile(path.join(OUT, name), buf);
    results.push(`${name}\t${buf.length}`);
  } catch (e) {
    results.push(`${name}\tFAILED ${e.message}`);
  }
}
console.log(results.join("\n"));
