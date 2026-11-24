#!/usr/bin/env node
/**
 * Generate a changelog from merged changes since the previous git tag.
 * Usage: npx tsx scripts/changelog.ts [--version v1.2.3]
 *
 * Run only after the version bump has been approved by the user.
 */

import { execSync } from "node:child_process";

function run(cmd: string): string {
  return execSync(cmd, { encoding: "utf8" }).trim();
}

const lastTag = (() => {
  try {
    return run("git describe --tags --abbrev=0");
  } catch {
    return "";
  }
})();

const range = lastTag ? `${lastTag}..HEAD` : "HEAD";
const commits = run(
  `git log ${range} --pretty=format:"%s" --no-merges`,
)
  .split("\n")
  .filter(Boolean);

const groups: Record<string, string[]> = {
  Added: [],
  Changed: [],
  Fixed: [],
  Removed: [],
  Other: [],
};

for (const subject of commits) {
  const line = subject.replace(/^(feat|fix|chore|docs|refactor|perf|test|build|ci)!?(\([^)]+\))?:\s*/i, "");
  const type = subject.match(/^(feat|fix|chore|docs|refactor|perf|test|build|ci)/i)?.[1]?.toLowerCase();
  if (type === "feat") groups.Added.push(line);
  else if (type === "fix") groups.Fixed.push(line);
  else if (type === "refactor" || type === "perf") groups.Changed.push(line);
  else if (/remov|drop/i.test(subject)) groups.Removed.push(line);
  else groups.Other.push(line);
}

const versionArg = process.argv.find((a) => a.startsWith("--version="));
const version = versionArg ? versionArg.split("=")[1] : "Unreleased";

const out: string[] = [`## ${version}`, ""];
for (const [name, items] of Object.entries(groups)) {
  if (!items.length) continue;
  out.push(`### ${name}`, "");
  for (const item of items) out.push(`- ${item}`);
  out.push("");
}

console.log(out.join("\n"));
