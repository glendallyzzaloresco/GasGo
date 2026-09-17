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

# Brevo Email Configuration (Uses HTTPS Port 443 - Works on Render Free Tier)
MAIL_MAILER=brevo
BREVO_API_KEY=your_brevo_api_key_here
MAIL_FROM_ADDRESS=gasgolpg@gmail.com
MAIL_FROM_NAME="GasGo"

# Supabase Storage S3-Compatible Settings
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=7b71b9ba39697cd9beb046062481badb
AWS_SECRET_ACCESS_KEY=ef5250bbdc97eba1ee9e399ed003f3431ed6ba4fc2ce552e921871dc7de40f8c
AWS_DEFAULT_REGION=ap-northeast-2
AWS_BUCKET=Gasgo
AWS_USE_PATH_STYLE_ENDPOINT=true
AWS_ENDPOINT=https://cleoitodbwfunzwudoot.storage.supabase.co/storage/v1/s3
AWS_URL=https://cleoitodbwfunzwudoot.supabase.co/storage/v1/object/public/Gasgo

