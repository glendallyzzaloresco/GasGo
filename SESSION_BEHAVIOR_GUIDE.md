# GasGo - Session & Login Behavior Guide

## Session Expiration Policy

### When Session Expires

Sessions in GasGo expire in the following situations:

1. **User clicks Logout button** → Session ends immediately ✓ **RECOMMENDED**
2. **User closes ALL browser tabs/windows** → Session expires when browser completely closes
3. **Inactivity timeout** → Session expires after 120 minutes of inactivity
4. **Admin/Server invalidates session** → For security reasons

### Important Multi-Tab Behavior

When you have **multiple browser tabs or windows open**:

```
Scenario:
────────
Tab 1: Admin Dashboard (Active)
Tab 2: Admin Dashboard (Close with X button)

Result:
───────
❌ Session is NOT expired
✓ Session remains active because Tab 1 is still open
✓ Opening new window still shows dashboard
```

**To properly close session with multiple tabs:**
- Click **Logout** button in any active tab, OR
- Close **ALL** browser tabs/windows

## Best Practices

### For Users:
1. **Always click Logout** before closing browser
   - This immediately terminates your session
   - Most secure option - doesn't rely on closing tabs
2. Close all tabs if you forget to logout
3. Don't share browser/device without logging out

### For Administrators:
1. Ensure riders, admins, and customers understand logout is important
2. Add reminder on login page: "Remember to logout before closing"
3. For shared devices, always logout explicitly

## Security Notes

- Session data is stored securely in database
- Cookies are HTTP-only (JavaScript cannot access)
- CSRF tokens protect against unauthorized requests
- Sessions auto-invalidate on logout regardless of open tabs

## Troubleshooting

### "I closed the browser but it still shows my dashboard"
- One or more tabs might still be open
- Close ALL tabs/windows and try again
- Or next login will start fresh session

### "I want to stay logged in across sessions"
- Use "Remember Me" checkbox on login (if available)
- This creates a persistent token valid for 30 days

---

**Last Updated:** March 20, 2026  
**Framework:** Laravel 11  
**Session Driver:** Database
