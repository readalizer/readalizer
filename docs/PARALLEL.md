# Parallel Execution

Readalizer can run analysis in parallel when `--jobs` is greater than 1.

## Overview

- `ParallelRunner` collects files and splits them into chunks.
- Each chunk is processed by a worker process running `--_worker` mode.
- Workers stream progress ticks to a temporary file and emit violations to a JSON file.
- The parent process merges violations and updates the progress bar.

## Worker Security

Worker mode is internal only and protected by:

- `READALIZER_INTERNAL=1` environment flag.
- A random token passed via `--worker-token` that must match the token in `--worker-token-file`.

The worker will refuse to run without valid credentials.

## Worker Flags

Internal flags used by workers:

- `--_worker`
- `--worker-files=<file>`
- `--worker-output=<file>`
- `--worker-progress=<file>`
- `--worker-token=<token>`
- `--worker-token-file=<file>`

Do not call these manually.

## Resource Limits

- CPU usage is capped at 50% of logical cores (`JobPlanner` enforces this).
- Memory is split evenly across workers based on the `--memory-limit` value.

## Progress

- Workers write a newline for each file they complete.
- The parent process counts these newlines to update the progress bar.

## See Also

- [CLI.md](CLI.md)
- [ARCHITECTURE.md](ARCHITECTURE.md)
