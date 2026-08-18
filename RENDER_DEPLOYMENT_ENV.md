DB_CONNECTION=mysql
DB_HOST=gateway01.ap-southeast-1.prod.aws.tidbcloud.com
DB_PORT=4000
DB_DATABASE=gasgo   
DB_USERNAME=27UpCwwpzdfiuyk.root
DB_PASSWORD=ZzTZCEcuy72MpRlT
MYSQL_ATTR_SSL_CA=/etc/ssl/certs/ca-certificates.crt
APP_NAME=GasGo
APP_ENV=production
APP_KEY=base64:8IARXbbOx76MzsVvNaTKHrcqYcWidu5WdKyGodpcUkw=
APP_DEBUG=false

# Resend Email Configuration (Uses HTTPS Port 443 - Works on Render Free Tier)
MAIL_MAILER=resend
RESEND_API_KEY=re_Y26vwGNR_PjHSCVSfaGAZtZjpqEcK1Bw3
MAIL_FROM_ADDRESS=onboarding@resend.dev
MAIL_FROM_NAME="GasGo"

# Cloudflare R2 Settings
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=760ecac8a8a907b8aec370b2adb9c398
AWS_SECRET_ACCESS_KEY=2f5ad4b10fd073ecab185251c71c31d363ae1e9dd555434dac2f298f977dee29
AWS_DEFAULT_REGION=auto
AWS_BUCKET=gasgo-assets
AWS_ENDPOINT=https://fa270ac2fbe07cac12ef328e8f355c72.r2.cloudflarestorage.com
AWS_URL=https://pub-034cbfc971d6455993f7ec82c6c55771.r2.dev
AWS_USE_PATH_STYLE_ENDPOINT=true
