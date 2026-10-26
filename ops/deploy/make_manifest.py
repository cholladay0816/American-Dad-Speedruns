#!/usr/bin/python3
"""Generate baseline fingerprints from an independently verified git archive; no extraction."""
from pathlib import Path
import sys

import deploy as d


def main():
    d.require(len(sys.argv) == 4, 'usage: make_manifest.py verified-git-archive.tar full-commit output.json')
    archive, commit, output = Path(sys.argv[1]), sys.argv[2], Path(sys.argv[3])
    d.require(d.SHA.fullmatch(commit), 'full immutable commit required')
    d.require(not output.exists(), 'refuse to replace an existing provenance record')
    expected = d.source_fingerprint(archive)
    d.require(expected, 'empty source archive')
    d.atomic_json(output, {'commit': commit, 'files': {name: digest for name, digest in expected.items()
                                                   if not name.startswith('storage/')}})


if __name__ == '__main__':
    main()
