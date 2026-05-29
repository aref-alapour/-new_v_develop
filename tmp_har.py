import json
import sys
sys.stdout.reconfigure(encoding="utf-8", errors="replace")
har = json.load(open(r"c:\Users\jobal\Desktop\team-sans_management.har", encoding="utf-8"))
entries = [e for e in har["log"]["entries"] if "/ajax" in e["request"]["url"]]
print("count", len(entries))
for e in sorted(entries, key=lambda x: -x.get("timings", {}).get("wait", 0)):
    h = {x["name"].lower(): x["value"] for x in e["response"]["headers"]}
    act = next((x["value"] for x in e["request"]["headers"] if x["name"].lower() == "x-ez-action"), "?")
    req = e["request"].get("postData", {}).get("text", "")[:60]
    resp = e["response"].get("content", {}).get("text", "")[:120]
    print(
        int(e["timings"].get("wait", 0)),
        "ms",
        act,
        "st",
        e["response"]["status"],
        "build",
        h.get("x-ez-gateway-build", "?"),
        "sess",
        h.get("x-ez-gateway-session", "-"),
        "reqE",
        req.startswith('{"ez_enc'),
        "respE",
        resp.startswith('{"ez_enc') or h.get("x-ez-response-encrypted") == "v1",
        "body",
        resp.replace("\n", " ")[:100],
    )
