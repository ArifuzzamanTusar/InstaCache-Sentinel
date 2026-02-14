# 🛡️ InstaCache Sentinel
**A high-performance, cost-saving microservice for syncing Instagram social proof.**

InstaCache Sentinel acts as a bridge between your high-traffic website and the Instagram API. It prevents "Rate Limit" issues and saves you money by caching follower data in a local database.

---

## 🚀 Key Features
* **Quota Protection:** Reduces API calls to exactly 1 per hour (24/day).
* **Zero Latency:** Visitors load data from your local DB, not a slow external API.
* **CORS Ready:** Pre-configured for cross-domain fetch requests.
* **SEO Friendly:** Ready to be rendered server-side via PHP.

---

## 🏗️ System Logic
1. Request hits `api endpoint`.
2. Script checks if `last_updated` is older than **3600 seconds** (1 hour).
3. **If Old:** Script hits RapidAPI, updates MySQL, and returns fresh JSON.
4. **If New:** Script returns data directly from MySQL (0 API cost).

---

## ⚙️ Installation

### 1. Database Configuration (SQL)
Run this in your MySQL manager to create the table and initialize the tracker:

```sql
CREATE TABLE `insta_cache` (
  `id` int(11) NOT NULL,
  `followers` int(11) DEFAULT 0,
  `last_updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
);

INSERT INTO `insta_cache` (`id`, `followers`, `last_updated`) 
VALUES (1, 0, '2023-01-01 00:00:00');
```

### API Deployment
Upload your index.php to your subdomain. Ensure your Database credentials and RapidAPI Key are updated in the config section of the script.
https://rapidapi.com/3205/api/instagram120

### ⚡ API Response Format
The endpoint returns a clean JSON object for easy consumption:

```
{
  "followers": 300,
  "username": "instagram_user",
  "last_updated": "2026-02-14 16:39:04",
  "cached": true
}
```
### 🔒 Security Best Practices
* **No-Index:** The API sends a noindex header to stay hidden from Google.
* **Key Rotation:** Change your RapidAPI key monthly via the dashboard.
* **Database Access:** Use a dedicated DB user with limited permissions.