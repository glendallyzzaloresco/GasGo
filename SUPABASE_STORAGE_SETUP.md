# Supabase Storage Setup Guide for GasGo

This guide walks you through configuring **Supabase Storage** (via its official S3-compatible API) as the cloud storage backend for GasGo, replacing Cloudflare R2.

---

## Step 1: Create a Public Bucket in Supabase

1. Open your [Supabase Dashboard](https://supabase.com/dashboard).
2. Select your project.
3. In the left sidebar, click on **Storage**.
4. Click **New Bucket**.
5. Set the bucket name to:
   ```text
   gasgo-assets
   ```
6. **IMPORTANT**: Toggle **"Public bucket"** to **ON**.
   > *Supabase controls public image access at the bucket level (or via RLS policies), not via standard AWS ACL headers.*
7. Click **Save**.

---

## Step 2: Generate S3 Credentials in Supabase

1. In your Supabase Dashboard, click on the **Settings (gear icon)** at the bottom of the left sidebar.
2. Under the *Configuration* section, click **Storage**.
3. Scroll down to the **S3 Access Keys** section.
4. If S3 protocol is not already enabled, enable it.
5. Click **Generate new access key** (or **Create Access Key**).
6. Supabase will generate and display:
   - **Endpoint**: `https://<PROJECT-REF>.supabase.co/storage/v1/s3`
   - **Access Key ID**: (e.g. `1234567890abcdef...`)
   - **Secret Access Key**: (e.g. `abcdef1234567890...`)
   - **Region**: (Matches your Supabase project region, e.g. `ap-southeast-1` or `us-east-1`)
7. **Copy both keys immediately** (the secret key is only shown once).

---

## Step 3: Update Your Local `.env`

Open your local `.env` file in `c:\laragon\www\GasGo---Capstone\.env` and configure:

```env
FILESYSTEM_DISK=s3

# Supabase Storage S3-Compatible Settings
AWS_ACCESS_KEY_ID=<your-supabase-s3-access-key-id>
AWS_SECRET_ACCESS_KEY=<your-supabase-s3-secret-access-key>
AWS_DEFAULT_REGION=<your-supabase-project-region>  # e.g., ap-southeast-1
AWS_BUCKET=gasgo-assets
AWS_USE_PATH_STYLE_ENDPOINT=true
AWS_ENDPOINT=https://<your-project-ref>.supabase.co/storage/v1/s3
AWS_URL=https://<your-project-ref>.supabase.co/storage/v1/object/public/gasgo-assets
```

*(Alternatively, you can use the `SUPABASE_STORAGE_*` prefix; GasGo supports both.)*

---

## Step 4: Sync All Existing Images to Supabase

Run the new artisan sync command in your terminal:

```bash
php artisan storage:sync-supabase
```

This command will:
* Recursively find all products, freebies, branding logos, and payment method images from your local `storage/app/public` folder.
* Detect their correct MIME types.
* Upload them directly to your Supabase `gasgo-assets` bucket without sending unsupported ACL headers.
* Skip files that already exist on Supabase (use `--force` if you want to overwrite).

---

## Step 5: Configure Production (Render)

When deploying to Render:
1. Go to your Web Service in the Render Dashboard.
2. Go to the **Environment** tab.
3. Add the exact same environment variables from Step 3:
   * `FILESYSTEM_DISK=s3`
   * `AWS_ACCESS_KEY_ID=...`
   * `AWS_SECRET_ACCESS_KEY=...`
   * `AWS_DEFAULT_REGION=...`
   * `AWS_BUCKET=gasgo-assets`
   * `AWS_USE_PATH_STYLE_ENDPOINT=true`
   * `AWS_ENDPOINT=https://<your-project-ref>.supabase.co/storage/v1/s3`
   * `AWS_URL=https://<your-project-ref>.supabase.co/storage/v1/object/public/gasgo-assets`
4. Trigger a deploy or let Render redeploy automatically.
