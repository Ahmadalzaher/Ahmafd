import json, re, sys
from concurrent.futures import ThreadPoolExecutor, as_completed
import requests
from bs4 import BeautifulSoup

BASE = "https://arab-kulturfestival.de"
UA = "Mozilla/5.0 (compatible; ClaudeSEO/2.0)"
pages = json.load(open("crawl-results.json", encoding="utf-8"))

def get(url):
    return requests.get(url, headers={"User-Agent": UA}, timeout=30)

def text_of(url):
    r = get(url)
    soup = BeautifulSoup(r.text, "lxml")
    for t in soup(["script", "style", "noscript", "nav", "footer", "header"]):
        t.decompose()
    main = soup.find("main") or soup.body or soup
    txt = re.sub(r"\s+", " ", main.get_text(" ", strip=True)).lower()
    return url, txt

def shingles(txt, n=5):
    words = re.findall(r"\w+", txt)
    return set(" ".join(words[i:i+n]) for i in range(max(0, len(words)-n+1)))

docs = {}
with ThreadPoolExecutor(max_workers=5) as ex:
    for f in as_completed([ex.submit(text_of, p["url"]) for p in pages]):
        try:
            u, t = f.result()
            docs[u] = t
        except Exception as e:
            print("err", e, file=sys.stderr)

sigs = {u: shingles(t) for u, t in docs.items()}
urls = sorted(sigs)
pairs = []
for i in range(len(urls)):
    for j in range(i+1, len(urls)):
        a, b = sigs[urls[i]], sigs[urls[j]]
        if not a or not b:
            continue
        inter = len(a & b)
        union = len(a | b)
        jac = inter / union if union else 0
        if jac >= 0.55:
            pairs.append((round(jac, 2), urls[i], urls[j]))
pairs.sort(reverse=True)
print(f"pages={len(docs)} near-duplicate pairs={len(pairs)}")
for p in pairs[:40]:
    print(p)
json.dump([{"similarity": p[0], "a": p[1], "b": p[2]} for p in pairs], open("duplicates.json", "w", encoding="utf-8"), ensure_ascii=False, indent=1)
