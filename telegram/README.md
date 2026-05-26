# telegram-notify — Usage Guide

## 1. What it is

A small set of Debian/Ubuntu shell tools to send notifications from a
server to a Telegram user or channel through a Telegram **bot**:

| File | Purpose |
|------|---------|
| `telegram-notify` | Main CLI. Sends text, photos, documents, audio, or GPS positions. |
| `telegram-notify.conf` | Default config file at `/etc/telegram-notify.conf`. Holds bot API key and recipient ID. |
| `rsync` (wrapper) | Drop-in `rsync` wrapper that adds a `--telegram <title>` flag for success/error notifications and basic transfer stats. |
| `telegram-notify-install.sh` | One-shot installer that pulls the latest files into `/etc/` and `/usr/local/bin/`. |

Hard dependency: **`curl`**. Soft dependencies: **`jq`** (safer JSON
parsing — strongly recommended), **`ffmpeg`** (only required for the
`--audio` option if your file is not already Opus), and **`locale`**
(for emoji/icon rendering on non-UTF-8 systems).

---

## 2. Prerequisites — bot and chat ID

Before the script can do anything, you need two values:

1. **Bot API key (token)** — obtained from
   [@BotFather](https://t.me/BotFather) in Telegram. Start a chat with
   BotFather, send `/newbot`, follow the prompts, and copy the token
   that looks like `1234567890:ABCDEFghijkl_mnoPQRstu-vwxyz`.

2. **User or channel ID** — the recipient the bot will message.
   - For a private chat: start a conversation with your bot in Telegram
     and send it any message. Then visit
     `https://api.telegram.org/bot<YOUR_TOKEN>/getUpdates` in a browser
     and look for `"chat":{"id":<number>,...}`. That number is your
     `user-id`.
   - For a channel: add the bot to the channel as an admin, post a
     message, then fetch `getUpdates` the same way. Channel IDs are
     usually negative (e.g. `-1001234567890`).
   - For a group: similar pattern; group IDs are negative.

The bot can only message users who have explicitly started a chat with
it. Channels and groups require the bot to be a member (admin for
channels).

---

## 3. Installation

### Automatic (one-shot installer)

The `telegram-notify-install.sh` script performs the full install:

```bash
sudo apt-get install -y curl jq
wget https://raw.githubusercontent.com/NicolasBernaerts/debian-scripts/master/telegram/telegram-notify-install.sh
sudo bash telegram-notify-install.sh
```

What it does:

- `apt-get install curl`
- Fetches `telegram-notify.conf` into `/etc/telegram-notify.conf`
- Fetches `telegram-notify` into `/usr/local/bin/telegram-notify` and
  marks it executable
- Fetches the `rsync` wrapper into `/usr/local/bin/rsync`

> **Heads up on the rsync wrapper.** Because `/usr/local/bin` precedes
> `/usr/bin` in the default `PATH`, installing the wrapper means every
> subsequent `rsync` call on the box goes through it. The wrapper just
> forwards arguments to `/usr/bin/rsync`, but if you don't want this,
> install it under a different name (e.g. `rsync-telegram`).

### Manual install

```bash
sudo apt-get install -y curl jq
sudo install -m 0644 telegram-notify.conf /etc/telegram-notify.conf
sudo install -m 0755 telegram-notify       /usr/local/bin/telegram-notify
# Optional: rsync wrapper
sudo install -m 0755 rsync                 /usr/local/bin/rsync
```

Then edit `/etc/telegram-notify.conf` (see below).

---

## 4. Configuration file

Default path: **`/etc/telegram-notify.conf`**
Override with `--config <file>` on the command line.

Format — INI-ish, but only `key=value` lines are read; section headers
are decorative. Everything after a `#` on a line is treated as a
comment. Keys are normalised: hyphens become underscores and the key
is upper-cased internally (so `api-key`, `API_KEY`, `api_key` all
work).

Recognised keys:

| Key | Meaning |
|-----|---------|
| `api-key` | Bot token from @BotFather. |
| `user-id` | Target user, group, or channel ID. |
| `socks-proxy` | Optional. `host:port` of a SOCKS5 proxy if outbound HTTPS to `api.telegram.org` is blocked. Passed to curl as `--socks5-hostname`. |

A minimal working config:

```ini
[general]
api-key=1234567890:ABCDEFghijkl_mnoPQRstu-vwxyz
user-id=987654321

[network]
socks-proxy=
```

**File permissions.** The token is a credential — treat the file
accordingly:

```bash
sudo chown root:root /etc/telegram-notify.conf
sudo chmod 0640      /etc/telegram-notify.conf
```

If non-root users  need to read it, place them in a group that owns the file and
keep mode `0640`, or use a separate config via `--config`.

---

## 5. CLI reference — `telegram-notify`

Running `telegram-notify` with no arguments prints the built-in help.
The full surface area:

### 5.1 Message content (pick one)

| Flag | Effect |
|------|--------|
| `--text <text>` | Send a text message. Use `--text -` to read the body from **stdin** (piped). |
| `--file <path>` | Read the body **from a file** and send as text. Useful for log dumps. |
| `--photo <path>` | Send an image. `--text` / `--title` become the caption. |
| `--document <path>` | Send a file as an attachment (any type). `--text` becomes the caption. |
| `--audio <path>` | Send an audio file as a Telegram **voice message**. Converted to Opus on the fly with `ffmpeg` if needed. |
| `--position <lat,long>` | Send a GPS location pin (e.g. `47.4763,8.3056`). |

Only the **last** content-type flag on the command line wins
(`--photo` set after `--text` will send a photo with `--text` as the
caption).

### 5.2 Formatting & message options

| Flag | Effect |
|------|--------|
| `--title <title>` | Prepend a bold title line above the message body. Bold-rendered as `*title*` (Markdown) or `<b>title</b>` (HTML). |
| `--html` | Switch parse mode from Markdown (default) to HTML. |
| `--disable_preview` | Don't expand link previews. |
| `--protect` | Sets `protect_content=true` so recipients can't forward, save, or copy the message. |
| `--silent` | Delivers without a notification sound/banner on the client. |
| `--quiet` | Suppresses **the script's own** stdout output. Does not affect what the recipient sees. |

### 5.3 Identity / config overrides

| Flag | Effect |
|------|--------|
| `--config <file>` | Use an alternate config file instead of `/etc/telegram-notify.conf`. |
| `--user <id>` | Override `user-id` from config. |
| `--key <token>` | Override `api-key` from config. |

Useful when a single host needs to send to multiple recipients (e.g.
per-service config files in `/etc/telegram-notify.d/`).

### 5.4 Built-in icon shortcuts

These prepend a Unicode glyph to the message. Pick at most one:

| Flag | Glyph | Use case |
|------|-------|----------|
| `--success` | ✅ | OK / job complete |
| `--warning` | ⚠ | Soft warning |
| `--error` | 🚨 | Hard failure / alert |
| `--question` | ❓ | Prompt / unclear state |
| `--exclamation` | ❗ | Attention |
| `--bug` | 🐛 | Software bug |
| `--beetle` | 🪲 | Hardware/other |
| `--icon <hex>` | any | Arbitrary Unicode code point, e.g. `--icon 1F355` for 🍕. |

The icon is emitted via `printf "%b"` and the script will probe
`locale -a` for a UTF-8 locale if `$LANG` is non-UTF-8, so emojis
render correctly even on minimal server installs (assuming the
`locales` package is present).

### 5.5 Debug flags

| Flag | Effect |
|------|--------|
| `--debug` | Print config values (API key masked) and the raw JSON returned by the Telegram API. |
| `--debug_key` | Same as `--debug`, but **also prints the API key in clear**. Never leave this in cron. |
| `--verbose` | Even noisier than `--debug`; implies `--debug` and turns off `--quiet`. |

---

## 6. Exit codes

| Code | Meaning |
|------|---------|
| `0` | Success — Telegram API returned `ok:true`. |
| `1` | Telegram API returned `ok:false`, or a pre-flight check failed (missing `curl`, missing config, missing file referenced by `--file`/`--photo`/etc., missing API key or user ID). |
| `2` | Nothing to send — no valid content type assembled. |

Use these in shell pipelines, cron jobs, and Icecast hook scripts to
chain follow-up actions.

---

## 7. How it works under the hood

The script just composes a `curl` call to the appropriate Telegram Bot
API endpoint:

| Content type | Endpoint |
|--------------|----------|
| `--text` / `--file` | `POST /bot<token>/sendMessage` (form-encoded `--data`) |
| `--photo` | `POST /bot<token>/sendPhoto` (multipart `--form`) |
| `--document` | `POST /bot<token>/sendDocument` (multipart `--form`) |
| `--audio` | `POST /bot<token>/sendVoice` (multipart `--form`, file must be Opus) |
| `--position` | `POST /bot<token>/sendLocation` |

`curl` always runs with `--silent --insecure`. If `socks-proxy` is
configured, `--socks5-hostname <host:port>` is appended.

Title handling is purely client-side: the title is wrapped in
`*…*` (Markdown) or `<b>…</b>` (HTML) and prepended with a newline
before the body. There's no separate Telegram field for it.

Audio handling: if the input isn't already Opus, the script invokes
`ffmpeg -i <input> -c libopus -ab 64k <tmp.ogg>` and uploads the
result. Without `ffmpeg` installed, `--audio` will fail with an error
and no message is sent.

JSON parsing: if `jq` is available, the response is parsed properly.
Without `jq`, a fragile fallback parser is used and a warning is
printed — install `jq`.

---

## 8. Examples

### 8.1 The simplest possible message

```bash
telegram-notify --text "Hello from $(hostname)"
```

### 8.2 With title and a success icon

```bash
telegram-notify --success \
                --title "Nightly backup" \
                --text  "Completed in 14m22s, 8.4 GiB transferred."
```

### 8.3 Pipe the tail of a log

```bash
tail -n 20 /var/log/nginx/error.log | telegram-notify \
    --warning --title "nginx error tail" --text -
```

Note the trailing `-` — that's the signal to read stdin.

### 8.4 Send the contents of a log file

```bash
telegram-notify --error \
                --title "fail2ban report" \
                --file  /var/log/fail2ban.log
```

Without `--file`, the script would send the literal string
`/var/log/fail2ban.log`, not its contents — a common pitfall.

### 8.5 Send a screenshot or graph

```bash
telegram-notify --photo /tmp/cpu-graph.png \
                --title "CPU last 24h" \
                --text  "Spike at 03:17 UTC."
```

### 8.6 Send a backup archive as a document

```bash
telegram-notify --document /var/backups/db-$(date +%F).sql.gz \
                --silent \
                --title "DB dump" \
                --text  "Daily snapshot"
```

Telegram caps `sendDocument` at 50 MB per file for bots.

### 8.7 Send a GPS position

```bash
telegram-notify --position "47.4763,8.3056" \
                --title "Service location" \
                --text  "Baden, AG"
```

### 8.8 Send a voice memo / Opus audio

```bash
telegram-notify --audio /tmp/alert.mp3 \
                --title "Audible alert" \
                --text  "Generated by alerting pipeline"
```

`ffmpeg` must be installed to transcode anything that isn't already
Opus.

### 8.9 Per-recipient config without touching `/etc/`

```bash
telegram-notify --config /etc/telegram-notify-ops.conf \
                --success --title "Deploy" --text "$1 deployed"
```

Or override inline:

```bash
telegram-notify --key "$BOT_TOKEN" \
                --user "$CHAT_ID" \
                --text "Ad-hoc message"
```

### 8.10 HTML formatting

```bash
telegram-notify --html --text \
  "Service <b>icecast2</b> restarted at <code>$(date -Is)</code>"
```

### 8.11 Custom emoji

```bash
telegram-notify --icon 1F4E1 --text "Signal acquired"   # 📡
telegram-notify --icon 1F525 --text "Hot path detected" # 🔥
```

---

## 9. The `rsync` wrapper

A thin shim around `/usr/bin/rsync` that adds **one** flag:

- `--telegram <title>` — after `rsync` exits, send a Telegram
  notification with the given title. Success uses ✅, failure uses 🚨.
  The message body includes the total and transferred sizes parsed
  from `rsync --stats`.

It always injects `--stats -h` into the underlying `rsync` call so it
can grep the size lines from output, and it preserves rsync's exit
code.

### Example

```bash
rsync -a --delete \
      --telegram "Mirror to bigtank01" \
      /srv/data/  zumbi@bigtank01:/volume1/archive/data/
```

On success you'll get a message like:

```
✅ Mirror to bigtank01
Total file size: 482.31G (2.14G transfered)
```

…and on failure the same body with 🚨 and a non-zero exit code
propagated to your shell / cron.

> Because the wrapper lives at `/usr/local/bin/rsync` and that
> directory precedes `/usr/bin` in `PATH`, **every** invocation of
> `rsync` on the host goes through it. If you don't want that, install
> the wrapper under a different name and call it explicitly.

---

## 10. Operational notes & gotchas

- **Markdown parse errors.** Telegram's "Markdown" mode is unforgiving
  about unbalanced characters. An odd number of `_`, `*`, `` ` ``, or
  `[` in the message body will produce
  `Bad Request: can't parse entities`. Workarounds: switch to
  `--html` and escape `<`, `>`, `&` manually, or sanitise the body
  upstream.

- **`--text` vs `--file`.** `--text "/var/log/foo.log"` sends the
  **path string**, not the file's contents. Use `--file
  /var/log/foo.log` instead.

- **Message length.** Telegram caps `sendMessage` at 4096 characters.
  Longer payloads will be rejected. Wrap log tails in `head -c 3500`
  or send them as `--document` instead.

- **Rate limits.** Bots are limited to roughly 30 messages/second
  globally and 1 message/second to the same chat. Bursty alerting
  pipelines should debounce.

- **Cron environment.** When running from cron, give the absolute
  path: `/usr/local/bin/telegram-notify`, and avoid `--debug_key` in
  any non-interactive job. Mail-on-failure (`MAILTO=`) plus a
  `--success`/`--error` notify gives you belt-and-braces coverage.

- **Running as a non-root user (Icecast hooks etc.).** If
  `telegram-notify` is invoked by a system user such as `icecast2`,
  ensure that user can read `/etc/telegram-notify.conf` (or point at
  a dedicated config via `--config`). Log files written by the hook
  must also be owned/writable by that same user, not root.

- **Install `jq`.** The fallback JSON parser is documented in the
  source as "quick'n'dirty and not so safe" and emits a warning on
  every run without it.

- **SOCKS5 proxy.** If outbound to `api.telegram.org` is blocked (some
  ISPs/jurisdictions), set `socks-proxy=host:port` in the config.
  The script passes this to curl as `--socks5-hostname`, so DNS is
  resolved on the proxy side.

- **TLS verification.** `curl` runs with `--insecure`. That means MITM
  on the Telegram API call is not detected. The script trusts the
  bot token to be the secret. If that matters in your threat model,
  patch the script to remove `--insecure` and rely on the system
  CA bundle.

---

## 11. Revision history (script-internal)

From the header of `telegram-notify`:

- v1.0 (2016-01-10) — initial release
- v1.1 — emoticon handling
- v1.2 — `--key` and `--user` CLI params; dropped Perl dependency
- v1.3 — `--document`, `--html`, `--silent`
- v1.4 — `--icon`
- v1.5 — piped text via `--text -`
- v1.6 — SOCKS5 proxy support
- v1.7 — `--warning`, `--config`
- v1.8 — `--file`
- v1.9 — `--quiet`
- v2.0 — `--disable_preview`
- v2.1 — `--protect`, code formatting fix
- v2.2 — bug fixes
- v2.3 — fixes to image and document sending
- v2.4 — `--debug`, `--position`
- v2.5 (2023-02-03) — `--audio` and a general rewrite

`rsync` wrapper:

- v1.0 (2022-02-20) — initial release
- v1.1 (2024-06-17) — added rsync stats parsing

---

## 12. License

The scripts are published under the original author's repository terms (see <https://github.com/NicolasBernaerts/debian-scripts>).

This great documentation has been written by [@tzumbrunnen](https://github.com/tzumbrunnen)
