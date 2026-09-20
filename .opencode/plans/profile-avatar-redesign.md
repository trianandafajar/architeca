# Plan: Profile Redesign & Avatar Upload

## Goal
Improve the profile page UI/UX to make it look professional (fixing the "ugly default form" look) and add support for uploading a profile picture (`avatar_url`).

## Steps for Implementation (After Plan Mode Approval)

1. **Database Migration:**
   - Add `avatar_url` nullable string column to `users` table. (Already generated & migrated: `2026_09_20_070927_add_avatar_url_to_users_table.php`).

2. **Model Update:**
   - Update `App\Models\User.php` to include `avatar_url` in `$fillable`.
   - Configure Filament's avatar provider if needed, or use the custom uploaded image.

3. **Custom EditProfile Form Layout:**
   - Update `App\Filament\Pages\Auth\EditProfile.php` to include a `FileUpload` component for `avatar_url` (disk: `public`, directory: `avatars`, image, circle cropping or avatar preset).
   - Group fields into clean sections with rich descriptions.

4. **Custom Profile View Styling:**
   - Create a polished view for profile editing that matches the rest of the application's design system (warm beige/orange accents, clean card containers, clear visual hierarchy).
   - Display current user avatar with fallback to initials.

5. **Cache Clearing:**
   - Run `php artisan optimize:clear` and `php artisan view:clear`.
