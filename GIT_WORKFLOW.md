# Git Workflow Guide for Vincent

This guide documents how to pull the latest changes from Glend's repository and push your updates.

---

## 1. Getting the Latest Changes (Pulling)

Since your project uses `upstream` to track Glend's original repository and `origin` to track your own copy, use the following commands to pull updates:

```bash
# 1. Switch to your local main branch
git checkout main

# 2. Fetch the latest changes from Glend's repository
git fetch upstream

# 3. Merge Glend's updates into your local main branch
git merge upstream/main

# 4. (Optional) Keep your own GitHub repository updated
git push origin main
```

---

## 2. Keeping Your Feature Branch Up to Date (Merging)

If you are working on a feature branch (e.g., `feature/vincent-fixes`) and want to pull Glend's latest changes **into your active branch**:

```bash
# 1. Make sure you are on your feature branch
git checkout feature/vincent-fixes

# 2. Fetch the latest changes from Glend
git fetch upstream

# 3. Merge Glend's main branch directly into your feature branch
git merge upstream/main
```

---

## 3. Pushing Your Changes (Working on Features)

Always write code in a feature branch instead of working directly on `main`.

```bash
# 1. Create and switch to a new branch for your task
git checkout -b feature/your-feature-name

# 2. Make your code changes, then stage and commit them
git add .
git commit -m "Describe what changes you made"

# 3. Push the branch to your own GitHub repository
git push origin feature/your-feature-name
```

---

## 4. Merging Into Glend's Main Project

1. Go to your repository on GitHub: [vincentagbuya03/GasGo---Capstone](https://github.com/vincentagbuya03/GasGo---Capstone).
2. Click **"Compare & pull request"** next to your pushed branch.
3. Verify the base repository is set to `glendallyzzaloresco/GasGo---Capstone` (branch: `main`).
4. Click **"Create pull request"**.
