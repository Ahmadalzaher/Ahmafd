import { writeFile } from "node:fs/promises";
const BASE = "https://arab-kulturfestival.de/wp-content/uploads/";
const OUT = "D:/oben code/_assets/";
const files = {
  "dabke-circle.jpg": "2023/09/1280x960-4.jpg",
  "kalligrafie.jpg": "2023/09/sriimg20081107_9942914_1-data.jpg",
  "arabic-music.jpg": "2023/09/thumb994.jpg",
  "festival-1.jpg": "2023/09/252.jpg",
  "festival-2.jpg": "2023/09/455214.jpg",
  "festival-3.jpg": "2023/09/55555.jpg",
  "festival-4.jpg": "2023/09/123456.jpg",
  "partner-bkm.png": "2024/06/behoerde-fur-kultur-und-medien-hamburg-logo-bkm-e1612337405177.png",
  "partner-goldbekhaus.png": "2023/10/logo_www_rot-Kopie.png",
  "gallery-3.jpg": "2022/12/gallery-03-free-img.jpeg",
};
for (const [name, rel] of Object.entries(files)) {
  const url = BASE + rel.split("/").map(encodeURIComponent).join("/");
  try {
    const res = await fetch(url);
    if (!res.ok) throw new Error(String(res.status));
    const buf = Buffer.from(await res.arrayBuffer());
    await writeFile(OUT + name, buf);
    console.log(name, buf.length);
  } catch (e) { console.log(name, "FAILED", e.message); }
}
