import json, re, sys, urllib.parse
from concurrent.futures import ThreadPoolExecutor, as_completed
import requests
from bs4 import BeautifulSoup

BASE = "https://arab-kulturfestival.de"
UA = "Mozilla/5.0 (compatible; ClaudeSEO/2.0; +https://github.com/AgriciDaniel/claude-seo)"
pages = json.load(open("crawl-results.json", encoding="utf-8"))

def get(url, timeout=30, method="get"):
    fn = requests.head if method == "head" else requests.get
    return fn(url, headers={"User-Agent": UA}, timeout=timeout, allow_redirects=True)

links = {}
imgs = set()
for p in pages:
    try:
        r = get(p["url"])
        soup = BeautifulSoup(r.text, "lxml")
        for a in soup.find_all("a", href=True):
            h = a["href"].strip()
            if h.startswith(("mailto:", "tel:", "javascript:", "#")):
                continue
            u = urllib.parse.urljoin(r.url, h).split("#")[0]
            if u.startswith(BASE):
                links[u] = a.get_text(" ", strip=True)[:80]
        for i in soup.find_all("img", src=True):
            u = urllib.parse.urljoin(r.url, i["src"])
            if u.startswith(BASE):
                imgs.add(u)
    except Exception as e:
        print("page error", p["url"], e, file=sys.stderr)

print(f"internal links: {len(links)} images: {len(imgs)}", file=sys.stderr)

def check(u):
    try:
        r = get(u, timeout=20)
        return {"url": u, "status": r.status_code, "final": r.url, "bytes": len(r.content), "type": r.headers.get("content-type","")}
    except Exception as e:
        return {"url": u, "status": None, "error": str(e)}

results = []
with ThreadPoolExecutor(max_workers=6) as ex:
    futs = {ex.submit(check, u): u for u in sorted(links)}
    for f in as_completed(futs):
        results.append(f.result())

img_results = []
with ThreadPoolExecutor(max_workers=6) as ex:
    futs = {ex.submit(check, u): u for u in sorted(imgs)}
    for f in as_completed(futs):
        img_results.append(f.result())

json.dump({"links": results, "images": img_results}, open("linkcheck.json", "w", encoding="utf-8"), ensure_ascii=False, indent=1)
print("done", file=sys.stderr)
