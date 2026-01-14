# Storage Architecture

## Overview

KLOAQ uses a dual-storage model optimized for privacy and ephemerality:

### 1. RAM-Only Storage (Ephemeral Content)
All user-generated content is stored in memory:
- **Posts** - Titles, content, votes
- **Comments** - Nested discussions
- **Votes** - Upvotes/downvotes with IP tracking

**Why RAM?**
- Content disappears when server restarts (true ephemerality)
- No forensic trail on disk
- No backup/recovery possible
- Forces fresh conversations
- Prevents indefinite data retention

**Implications:**
- Restarting the server wipes all posts and comments
- This is a feature, not a bug
- Users know their content is truly temporary

### 2. Encrypted SQLite Database (User Accounts)
User accounts are stored persistently in an encrypted SQLite database:
- **Username** - Unique identifier
- **Password Hash** - Argon2ID (memory-hard, GPU-resistant)
- **Created At** - Account creation timestamp
- **Banned** - Account ban status

**Why SQLite with encryption?**
- Lightweight (no separate database server)
- Portable single-file database
- Easy to backup/delete
- Can be encrypted at filesystem level
- Policy enforcement without device tracking

**Security:**
- Uses Argon2ID for password hashing (superior to bcrypt)
- Database encryption key set via environment variable
- Accounts can be banned without tracking IPs long-term
- No personal information stored

## Configuration

### Setting Database Encryption Key

**Production (recommended):**
```bash
export KLOAQ_DB_KEY="your-secure-random-key-here"
./run.sh
```

**Generate a secure key:**
```bash
openssl rand -base64 32
```

**Persistent key (add to ~/.bashrc or systemd service):**
```bash
echo 'export KLOAQ_DB_KEY="your-key-here"' >> ~/.bashrc
```

### Additional Encryption (Optional)

For maximum security, encrypt the entire database file:

**Using LUKS (Linux):**
```bash
# Create encrypted volume
sudo cryptsetup luksFormat /dev/sdX
sudo cryptsetup open /dev/sdX kloaq_db
sudo mkfs.ext4 /dev/mapper/kloaq_db

# Mount and use
sudo mount /dev/mapper/kloaq_db /mnt/kloaq_data
# Point DATA_DIR to /mnt/kloaq_data in lib.php
```

**Using eCryptfs (user-level):**
```bash
# Encrypt data directory
sudo apt-get install ecryptfs-utils
mount -t ecryptfs ~/work/kloaq/public/data ~/work/kloaq/public/data
```

## Privacy Implications

### What Gets Wiped on Restart
- All posts and their votes
- All comments and their votes
- All vote history (IP-based anti-spam)

### What Persists
- User accounts (username + password hash)
- Ban status

### Attack Surface
The only persistent data is:
1. Account credentials (heavily hashed)
2. Ban flags (binary, no context)

No content, no IP logs, no metadata beyond what's needed for basic moderation.

## API Functions

### Content (RAM-Only)
```php
create_post($title, $content)         // Returns post ID
create_comment($post_id, $parent_id, $content)  // Returns comment ID
vote($type, $id, $value)              // Votes on post/comment
get_posts($sort)                      // Retrieves all posts
get_post($id)                         // Gets single post
get_comments($post_id, $parent_id)    // Gets comments (nested)
```

### Accounts (Encrypted Database)
```php
create_account($username, $password)  // Returns true/false
verify_account($username, $password)  // Returns true/false
ban_account($username)                // Bans an account
is_banned($username)                  // Checks ban status
```

## Threat Model

**Protects Against:**
- Long-term content retention
- Accidental data leaks (server seizure)
- Subpoenas for old content (doesn't exist)
- Bulk data harvesting

**Does Not Protect Against:**
- Live memory dumps while server is running
- Real-time monitoring/surveillance
- Social engineering
- Compromised server OS

## Best Practices

1. **Restart regularly** - Clear memory, force fresh content
2. **Rotate encryption key** - Periodically regenerate DB key
3. **Monitor account abuse** - Ban bad actors, they'll have to re-register
4. **No backups** - Embrace ephemerality, don't backup RAM content
5. **Minimal logs** - Don't log IP addresses or User-Agents in web server

## Technical Details

### Memory Usage
- Each post: ~1KB average (title + content + metadata)
- Each comment: ~500 bytes average
- 10,000 posts + 50,000 comments ≈ 35MB RAM

Scales well for small-to-medium communities. For larger deployments, add automatic pruning of old content.

### Performance
- RAM access: ~100ns latency
- No disk I/O for content operations
- Database queries only for auth (infrequent)
- Scales horizontally with session affinity

## Migration from JSON Files

If you have existing `posts.json`, `comments.json`, or `votes.json`, they are ignored. The new system starts fresh in RAM.

To preserve old content temporarily:
```bash
# Backup old data
cp public/data/*.json public/data/backup/

# Old data won't be loaded automatically
# This is intentional - embrace the fresh start
```
