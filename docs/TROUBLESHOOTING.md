# Troubleshooting

## Readalizer reports no progress

- Ensure `--no-progress` is not set.
- Confirm that the terminal supports ANSI output if you expect a styled bar.

## Worker processes hang

- Increase `--worker-timeout`.
- Reduce `--jobs` if the target filesystem is slow.
- Check system limits for process creation.

## Memory exhaustion

- Lower `--jobs`.
- Increase `--memory-limit`.
- Exclude large vendor or generated directories via `ignore`.

## JSON output includes extra lines

Run timing is written to `STDERR` when using `--format=json` so `STDOUT` remains valid JSON.

## See Also

- [CLI.md](CLI.md)
- [PARALLEL.md](PARALLEL.md)
