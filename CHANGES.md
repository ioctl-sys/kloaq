## Changes Summary

### Storage Architecture Redesign

The system now uses a **dual-storage model** for maximum privacy:

#### 1. RAM-Only Storage (Ephemeral)
- **Posts** - All post content, titles, and votes
- **Comments** - All discussion threads
- **Votes** - Anti-spam IP tracking for votes

**Key Feature:** All content is wiped when the server restarts. No disk writes, no forensic trail.

#### 2. Encrypted Database (Persistent)
- **User Accounts** - Username, Argon2ID password hash, ban status
- **Database:** SQLite at `public/data/accounts.db`
- **Encryption:** Set via `KLOAQ_DB_KEY` environment variable

### Modified Files

**[lib.php](public/lib.php)**
- Added `MemoryStore` class for RAM-based storage
- Replaced all file I/O operations with in-memory arrays
- Added encrypted SQLite database for accounts
- Added user management functions: `create_account()`, `verify_account()`, `ban_account()`, `is_banned()`
- Uses Argon2ID for password hashing (superior to bcrypt)

**[README.md](README.md)**
- Updated with new quick start instructions
- Added encryption key setup
- Added warning about ephemeral storage
- Link to STORAGE.md for details

**New Files:**
- **[STORAGE.md](STORAGE.md)** - Complete architecture documentation
  - Threat model
  - Security best practices
  - Configuration guide
  - API reference
  - Encryption options

### Usage

```bash
# Set encryption key (recommended)
export KLOAQ_DB_KEY="$(openssl rand -base64 32)"

# Run the server
./run.sh
```

### Security Features

✅ **Posts/comments never touch disk** - RAM only  
✅ **Argon2ID password hashing** - GPU-resistant  
✅ **Database encryption ready** - Set via env var  
✅ **No IP logging** - Only temporary vote anti-spam  
✅ **Content auto-wipes** - Server restart clears everything  
✅ **Account-based moderation** - Ban users, not devices  

### Privacy Implications

**Wiped on restart:**
- All posts and votes
- All comments and discussions  
- Vote history and IP tracking

**Persists across restarts:**
- User accounts (username + encrypted password)
- Ban status only

This makes server seizure or subpoenas for old content impossible - the content simply doesn't exist after restart.
