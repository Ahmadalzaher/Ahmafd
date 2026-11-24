import json, re, urllib.parse
from concurrent.futures import ThreadPoolExecutor, as_completed
import requests
from bs4 import BeautifulSoup

BASE = "https://arab-kulturfestival.de"
UA = "Mozilla/5.0 (compatible; ClaudeSEO/2.0)"
pages = [p["url"] for p in json.load(open("crawl-results.json", encoding="utf-8"))]

def links_of(u):
    r = requests.get(u, headers={"User-Agent": UA}, timeout=30)
    soup = BeautifulSoup(r.text, "lxml")
    out = set()
    for a in soup.find_all("a", href=True):
        h = a["href"].strip()
        if h.startswith(("mailto:", "tel:", "javascript:", "#")):
            continue
        t = urllib.parse.urljoin(r.url, h).split("#")[0].rstrip("/")
        if t.startswith(BASE):
            out.add(t)
    return u.rstrip("/"), out

graph = {}
with ThreadPoolExecutor(max_workers=5) as ex:
    for f in as_completed([ex.submit(links_of, u) for u in pages]):
        src, out = f.result()
        graph[src] = out

inbound = {u: set() for u in graph}
for src, outs in graph.items():
    for t in outs:
        if t in inbound and t != src:
            inbound[t].add(src)

orphans = [u for u, ins in inbound.items() if not ins]
print("pages:", len(graph), "orphans (no inbound internal link):", len(orphans))
for u in sorted(orphans):
    print("  ", u)
# depth from home
home = BASE
depth = {home: 0}
frontier = [home]
while frontier:
    nxt = []
    for u in frontier:
        for t in graph.get(u, []):
            if t not in depth:
                depth[t] = depth[u] + 1
                nxt.append(t)
    frontier = nxt
print("max depth:", max(depth.values()) if depth else None)
deep = [(d, u) for u, d in depth.items() if d >= 4]
print("pages depth >=4:", len(deep))
for d, u in sorted(deep)[:30]:
    print("  ", d, u)
unreachable = [u for u in graph if u not in depth]
print("unreachable from home:", len(unreachable))
for u in unreachable[:30]:
    print("  ", u)
json.dump({"orphans": sorted(orphans), "depth": {u: depth.get(u) for u in graph}}, open("linkgraph.json", "w", encoding="utf-8"), ensure_ascii=False, indent=1)
