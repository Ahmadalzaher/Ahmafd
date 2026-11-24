import json, re, sys, time, urllib.parse
from concurrent.futures import ThreadPoolExecutor, as_completed
import requests
from bs4 import BeautifulSoup

BASE = "https://arab-kulturfestival.de"
UA = "Mozilla/5.0 (compatible; ClaudeSEO/2.0; +https://github.com/AgriciDaniel/claude-seo)"
SITEMAPS = ["post-sitemap.xml", "page-sitemap.xml", "yd_event-sitemap.xml", "yd_service-sitemap.xml", "category-sitemap.xml"]

def get(url, timeout=30):
    return requests.get(url, headers={"User-Agent": UA, "Accept-Language": "de-DE,de;q=0.9,en;q=0.8"}, timeout=timeout, allow_redirects=True)

def locs():
    urls = []
    for s in SITEMAPS:
        r = get(f"{BASE}/{s}")
        urls += re.findall(r"<loc>(.*?)</loc>", r.text)
    # also known nav pages not necessarily in sitemap
    return sorted(set(u.replace("&amp;", "&") for u in urls))

def analyze(url):
    out = {"url": url}
    t0 = time.time()
    try:
        r = get(url)
        out["status"] = r.status_code
        out["final_url"] = r.url
        out["redirects"] = len(r.history)
        out["bytes"] = len(r.content)
        out["ms"] = round((time.time()-t0)*1000)
        out["content_type"] = r.headers.get("content-type","")
        out["x_robots"] = r.headers.get("x-robots-tag","")
        soup = BeautifulSoup(r.text, "lxml")
        html = soup.find("html")
        out["lang"] = html.get("lang") if html else None
        out["dir"] = html.get("dir") if html else None
        title = soup.find("title")
        out["title"] = title.get_text(strip=True) if title else None
        out["title_len"] = len(out["title"] or "")
        md = soup.find("meta", attrs={"name":"description"})
        out["meta_description"] = md.get("content","").strip() if md else None
        out["meta_description_len"] = len(out["meta_description"] or "")
        rob = soup.find("meta", attrs={"name":"robots"})
        out["meta_robots"] = rob.get("content") if rob else None
        can = soup.find("link", rel=lambda v: v and "canonical" in v)
        out["canonical"] = can.get("href") if can else None
        out["canonical_self"] = bool(out["canonical"] and out["canonical"].rstrip("/") == out["final_url"].rstrip("/"))
        h1s = soup.find_all("h1")
        out["h1"] = [h.get_text(" ", strip=True) for h in h1s]
        out["h1_count"] = len(h1s)
        out["h2_count"] = len(soup.find_all("h2"))
        out["h3_count"] = len(soup.find_all("h3"))
        schemas = []
        for s in soup.find_all("script", type="application/ld+json"):
            try:
                data = json.loads(s.string or "")
                if isinstance(data, dict):
                    if "@graph" in data and isinstance(data["@graph"], list):
                        schemas += [x.get("@type") for x in data["@graph"] if isinstance(x, dict)]
                    else:
                        schemas.append(data.get("@type"))
                elif isinstance(data, list):
                    schemas += [x.get("@type") for x in data if isinstance(x, dict)]
            except Exception:
                schemas.append("PARSE_ERROR")
        out["schema_types"] = schemas
        for tag in soup(["script","style","noscript"]):
            tag.decompose()
        text = soup.get_text(" ", strip=True)
        out["word_count"] = len(re.findall(r"\w+", text))
        imgs = soup.find_all("img")
        out["image_count"] = len(imgs)
        out["images_missing_alt"] = sum(1 for i in imgs if not (i.get("alt") or "").strip())
        out["images_no_dims"] = sum(1 for i in imgs if not (i.get("width") and i.get("height")))
        out["images_lazy"] = sum(1 for i in imgs if i.get("loading") == "lazy")
        out["hreflang"] = [(l.get("hreflang"), l.get("href")) for l in soup.find_all("link", rel=lambda v: v and "alternate" in v) if l.get("hreflang")]
        links = [a.get("href") for a in soup.find_all("a", href=True)]
        internal = [urllib.parse.urljoin(r.url, h) for h in links if urllib.parse.urljoin(r.url, h).startswith(BASE)]
        out["internal_links"] = len(set(internal))
        out["external_links"] = len(set(h for h in links if h.startswith("http") and not h.startswith(BASE)))
        out["noindex"] = "noindex" in (out["meta_robots"] or "").lower() or "noindex" in out["x_robots"].lower()
        out["text_sample"] = text[:300]
    except Exception as e:
        out["status"] = None
        out["error"] = str(e)
    return out

if __name__ == "__main__":
    urls = locs()
    print(f"URLS {len(urls)}", file=sys.stderr)
    results = []
    with ThreadPoolExecutor(max_workers=5) as ex:
        futs = {ex.submit(analyze, u): u for u in urls}
        for i, f in enumerate(as_completed(futs), 1):
            results.append(f.result())
            if i % 10 == 0:
                print(f"done {i}/{len(urls)}", file=sys.stderr)
    results.sort(key=lambda x: x["url"])
    with open(sys.argv[1] if len(sys.argv) > 1 else "crawl-results.json", "w", encoding="utf-8") as fh:
        json.dump(results, fh, ensure_ascii=False, indent=1)
    print(f"WROTE {len(results)}", file=sys.stderr)
