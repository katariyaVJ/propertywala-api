# API Architecture & Production Endpoints Specification
**Project:** Propertywala App (Real Estate & Bank Seized Distressed Assets Platform)  
**Role:** Principal Backend & API Architect (10+ Years Experience)  
**Standards:** RESTful Level 3, OpenAPI 3.0 Compatible, JSON:API Envelope, JWT RBAC Auth, Redis Caching

---

## 1. High-Performance API Engineering Standards

To guarantee **<100ms ultra-low latency**, 60fps smooth scrolling, and zero app freeze:

1. **Standard Global Envelope Format**:
   Every response (success or failure) follows a strict contract matching Dart deserializers:
   ```json
   {
     "success": true,
     "statusCode": 200,
     "message": "Operation completed successfully",
     "data": {},
     "meta": {
       "page": 1,
       "limit": 20,
       "total": 128,
       "totalPages": 7
     }
   }
   ```
   **Standard Error Format**:
   ```json
   {
     "success": false,
     "statusCode": 400,
     "message": "Validation error",
     "errors": [
       {"field": "phone", "message": "Phone number must be a valid 10-digit mobile"}
     ]
   }
   ```

2. **Security & Contact Privacy Engine (Zero Direct Leakage)**:
   - When returning property details (`GET /api/v1/properties/:id`):
     - The backend decodes the caller's JWT, verifies active Pro plan status from `pro_memberships`, and checks `pro_contact_unlocks`.
     - **For Free Users**: `{"sellerPhone": "+91 98251 XXXXX", "isMasked": true, "unlockAction": "PRO_REQUIRED"}`.
     - **For Active Pro Users**: `{"sellerPhone": "+91 98251 45678", "isMasked": false, "remainingQuota": 39}`.

3. **Keyset / Cursor Pagination for Infinite Scroll Feeds**:
   - Querying with `limit=20&cursor=timestamp` prevents duplicate cards or missing items during live scrolling.

4. **Multi-Tier Caching with Redis**:
   - `cities` and `famous_localities`: 24-hour cache TTL.
   - `new_projects` & `system_config`: 6-hour cache TTL.
   - `properties` feed: 60-second micro-cache invalidating instantly on new property approvals.

---

## 2. Complete API Endpoints Catalog (Module-by-Module)

### 2.1. Authentication & Session Module (`/api/v1/auth`)

#### 1. `POST /api/v1/auth/register`
* **When Called in App:** `lib/features/auth/register_screen.dart` on "Register" tap.
* **Request Headers:** `Content-Type: application/json`
* **Request Body:**
  ```json
  {
    "name": "Vijay Sharma",
    "phone": "9876543210",
    "password": "Password123",
    "city": "Surat",
    "role": "Property Owner"
  }
  ```
* **Success Response (201 Created):**
  ```json
  {
    "success": true,
    "statusCode": 201,
    "message": "User registered successfully",
    "data": {
      "user": {
        "id": "u_98214",
        "name": "Vijay Sharma",
        "phone": "9876543210",
        "email": null,
        "city": "Surat",
        "role": "Property Owner",
        "isProMember": false
      },
      "tokens": {
        "accessToken": "eyJhbGciOi...",
        "refreshToken": "d82f71...",
        "expiresIn": 86400
      }
    }
  }
  ```

#### 2. `POST /api/v1/auth/login`
* **When Called in App:** `lib/features/auth/login_screen.dart` on "Login" tap.
* **Request Body:** `{"phone": "9876543210", "password": "Password123"}`
* **Success Response (200 OK):** Returns JWT token pair, user profile, active Pro membership status, and remaining contact quota.

#### 3. `POST /api/v1/auth/refresh-token`
* **When Called in App:** Triggered automatically by Flutter `ApiClient` Dio interceptor upon receiving `401 Unauthorized`.
* **Request Body:** `{"refreshToken": "d82f71..."}`
* **Success Response (200 OK):** Returns new `accessToken` without forcing user logout.

#### 4. `GET /api/v1/auth/profile`
* **When Called in App:** `lib/features/profile/profile_screen.dart` on screen mount.
* **Headers:** `Authorization: Bearer <token>`
* **Success Response (200 OK):**
  ```json
  {
    "success": true,
    "data": {
      "id": "u_98214",
      "name": "Vijay Sharma",
      "phone": "+91 98765 43210",
      "email": "vijay.sharma@example.com",
      "city": "Surat",
      "role": "Property Owner",
      "isProMember": true,
      "proPlanName": "Standard Pro",
      "unlockedContactsRemaining": 38,
      "stats": {
        "postedListingsCount": 2,
        "shortlistsCount": 5
      }
    }
  }
  ```

#### 5. `PUT /api/v1/auth/profile`
* **When Called in App:** `lib/features/profile/edit_profile_screen.dart` on "Save Changes" tap.
* **Request Body:**
  ```json
  {
    "name": "Vijay Sharma",
    "email": "vijay.sharma@example.com",
    "altPhone": "9825177410",
    "city": "Surat",
    "role": "Property Owner",
    "notificationUpdates": true
  }
  ```

#### 6. `POST /api/v1/auth/logout`
* **When Called in App:** `ProfileScreen` logout confirmation dialog. Invalidates token in Redis session store.

---

### 2.2. Properties & Bank Seized Assets Module (`/api/v1/properties`)

#### 7. `GET /api/v1/properties` (Feed & Listing Screen)
* **When Called in App:** `lib/features/home/home_screen.dart` and `property_list_screen.dart`.
* **Query Parameters:**
  - `city` (string): e.g. `Surat`
  - `intent` (string): `Sell`, `Rent`, `Bank Auction`
  - `assetType` (string): `Property`, `Vehicle`
  - `propertyCategory` (string): `Residential`, `Commercial`, `Plots / Land`
  - `subType` (string): `Apartment / Flat`, `Tenament / Row House`, `Car / SUV`, etc.
  - `bhk` (string): `1 BHK`, `2 BHK`, `3 BHK`, `4 BHK`
  - `minPrice` (numeric) / `maxPrice` (numeric)
  - `search` (string): Preferred locality, society, or title
  - `sort` (string): `Relevance`, `Price: Low to High`, `Price: High to Low`, `Newest`
  - `page` (int, default: 1), `limit` (int, default: 20)
* **Success Response (200 OK):** Paginated array of property cards formatted for direct Flutter card mapping.

#### 8. `GET /api/v1/properties/by-code/:code` (Admin & User Direct Code Search)
* **When Called in App:** When user enters `032` or `PW-101` in the Home search bar or List screen.
* **Backend Logic:** Exact indexed match on `property_code`. Response time <15ms.
* **Success Response (200 OK):**
  ```json
  {
    "success": true,
    "statusCode": 200,
    "data": {
      "id": "p_101",
      "propertyCode": "032",
      "title": "3 BHK Luxury Apartment in Godrej Woods",
      "priceDisplay": "₹ 1.45 Cr",
      "pricePerSqft": "@ ₹ 8,500/sq.ft",
      "locality": "Sector 43, Noida",
      "bhk": "3 BHK",
      "area": "1,700 sq.ft",
      "postedBy": "Owner",
      "imageUrl": "https://cdn.propertywala.com/props/cover_p101.webp",
      "isVerified": true
    }
  }
  ```

#### 9. `GET /api/v1/properties/:id` (Property Details View)
* **When Called in App:** On tapping any property card (`lib/features/property/property_details_screen.dart`).
* **Success Response (200 OK):** Returns all details, up to 4 photo URLs with labels, specs, amenities grid, location coords, and masked/unmasked seller phone based on Pro status.

#### 10. `POST /api/v1/properties` (Post Property Wizard)
* **When Called in App:** On Step 3 submission in `lib/features/post_property/post_property_screen.dart`.
* **Request Payload (Real Estate Mode):**
  ```json
  {
    "intent": "Sell",
    "userRole": "Owner",
    "subType": "Apartment / Flat",
    "bhk": "2 BHK",
    "bathrooms": "2 Baths",
    "balconies": "1 Balcony",
    "price": 4500000,
    "areaValue": 1200,
    "areaUnit": "Sq.Ft",
    "areaType": "Carpet Area",
    "furnishing": "Semi-Furnished",
    "isNegotiable": true,
    "society": "Green City",
    "locality": "Vesu",
    "city": "Surat",
    "amenities": ["Lift / Elevator", "Reserved Parking", "24/7 Security"],
    "photos": [
      {"url": "https://cdn.propertywala.com/tmp/p1.webp", "label": "Living Room (Cover Photo)", "isCover": true},
      {"url": "https://cdn.propertywala.com/tmp/p2.webp", "label": "Master Bedroom", "isCover": false}
    ],
    "contactPhone": "9876543210"
  }
  ```
* **Request Payload (Banker Seized Vehicle Mode):**
  ```json
  {
    "intent": "Bank Auction",
    "userRole": "Banker",
    "assetType": "Vehicle",
    "vehicleCategory": "Car / SUV",
    "vehicleModel": "Toyota Fortuner 4x4",
    "vehicleMakeYear": 2022,
    "vehicleKmDriven": 35000,
    "vehicleOwnerType": "1st Owner",
    "vehicleFuelType": "Diesel",
    "vehicleTransmission": "Automatic",
    "vehicleRegNumber": "GJ-05-AB-1234",
    "bankName": "SBI",
    "auctionDate": "15 Nov 2026",
    "reservePrice": 1450000,
    "emdAmount": 145000,
    "bankBranchOrYard": "Vesu Yard, Surat",
    "vehicleFeatures": ["Valid Insurance", "Clear RC Book", "Good Tyres", "Bank Seized NOC"],
    "contactPhone": "9876543210",
    "photos": [...]
  }
  ```
* **Success Response (201 Created):** Returns generated ID, status (`Under Review`), and generated Unique Property Code (`033`).

#### 11. `GET /api/v1/properties/my/listings` (My Properties Management)
* **When Called in App:** `lib/features/profile/my_properties_screen.dart`.
* **Headers:** `Authorization: Bearer <token>`
* **Success Response (200 OK):** Returns all listings created by the authenticated user with real-time views and leads count.

#### 12. `DELETE /api/v1/properties/:id`
* **When Called in App:** On clicking "Delete" inside delete confirmation dialog in `MyPropertiesScreen`.

#### 13. `POST /api/v1/properties/upload-image`
* **When Called in App:** On selecting photos inside the 4-photo picker sheet.
* **Payload:** `multipart/form-data` file upload. Returns CDN image URL and WebP thumbnail.

---

### 2.3. Bank Auctions Module (`/api/v1/bank-auctions`)

#### 14. `GET /api/v1/bank-auctions`
* **When Called in App:** `lib/features/home/bank_auction_screen.dart`.
* **Query Parameters:**
  - `category`: `All`, `Properties`, `Cars`, `Other Vehicles`
  - `bank`: `SBI`, `HDFC Bank`, `ICICI Bank`, `BOB`, `Axis Bank`, `Kotak Bank`, `Canara Bank`, `PNB`
  - `city`: `Surat`
* **Success Response (200 OK):** Real-time list merging banker posted distressed properties and seized vehicles (bikes, scooters, trucks, cars).

#### 15. `GET /api/v1/bank-auctions/:id`
* **When Called in App:** `lib/features/home/bank_auction_details_screen.dart`.
* **Success Response (200 OK):** Full SARFAESI details, reserve price, EMD amount, stockyard address, vehicle inspection checklist, and official notice download URL.

---

### 2.4. Buyer Inquiries & Leads Module (`/api/v1/leads`)

#### 16. `POST /api/v1/leads/contact-seller` (Lead Creation)
* **When Called in App:** On tapping "Send Contact to Seller" or "I'm Interested" in `lib/features/property/widgets/contact_owner_sheet.dart`.
* **Request Body:**
  ```json
  {
    "propertyId": "p_101",
    "buyerName": "Vijay Sharma",
    "buyerPhone": "9876543210",
    "intent": "Self Use",
    "timeline": "Immediately",
    "source": "Contact Seller"
  }
  ```
* **Backend Processing:**
  1. Inserts record into `buyer_leads`.
  2. Database trigger increments `leads_count` on `properties`.
  3. Sends FCM push alert to the property seller.
* **Success Response (201 Created):**
  ```json
  {
    "success": true,
    "statusCode": 201,
    "message": "Inquiry sent successfully to seller",
    "data": {
      "leadId": "lead_9918",
      "sellerContact": {
        "name": "Rajesh Mehta",
        "role": "Direct Owner",
        "phone": "+91 98251 45678", -- Masked with blur if user is non-pro
        "isMasked": false
      }
    }
  }
  ```

#### 17. `GET /api/v1/leads/property/:propertyId` (Seller Lead Dashboard)
* **When Called in App:** `lib/features/profile/property_leads_screen.dart`.
* **Access Control:** Only the owner of the property can view this.
* **Success Response (200 OK):** Returns leads containing *strictly* Buyer Name, Mobile Number, and Timestamp per user mandate.

---

### 2.5. Shortlists / Saved Properties Module (`/api/v1/saved`)

#### 18. `GET /api/v1/saved` (Saved Screen)
* **When Called in App:** `lib/features/saved/saved_screen.dart`.
* **Headers:** `Authorization: Bearer <token>`
* **Success Response (200 OK):** Synchronized array of all properties bookmarked by user.

#### 19. `POST /api/v1/saved/toggle/:propertyId` (Heart Toggle)
* **When Called in App:** On tapping Heart (❤️) icon on any card or details screen.
* **Success Response (200 OK):** `{"success": true, "data": {"isSaved": true, "totalSaved": 6}}`.

---

### 2.6. Pro Membership & Contact Quota Module (`/api/v1/pro`)

#### 20. `GET /api/v1/pro/plans`
* **When Called in App:** `lib/features/home/pro_membership_screen.dart` on load.
* **Success Response (200 OK):** Returns plans: Starter (₹48 / 3 contacts), Standard Pro (₹499 / 40 contacts), Unlimited VIP (₹1,499 / unlimited).

#### 21. `POST /api/v1/pro/create-order`
* **When Called in App:** On tapping "Confirm & Pay" on `ProMembershipScreen`.
* **Request Body:** `{"planName": "Standard Pro"}`. Returns Razorpay/Gateway `order_id`.

#### 22. `POST /api/v1/pro/verify-payment`
* **When Called in App:** On payment gateway completion callback.
* **Request Body:** `{"orderId": "order_123", "paymentId": "pay_456", "signature": "sig_789"}`.
* **Backend Processing:** Validates signature, activates Pro plan (+30 days), seeds 40 contact credits into `pro_memberships`.

#### 23. `POST /api/v1/pro/unlock-contact`
* **When Called in App:** When a user taps to unblur/copy seller phone number in `ContactOwnerSheet` or `HomeLoanScreen`.
* **Request Body:** `{"propertyId": "p_101"}`.
* **Backend Processing:** Checks active Pro plan and remaining quota. Uses optimistic concurrency lock (`opt_lock_version`). If not already unlocked, records entry in `pro_contact_unlocks` and decrements quota by 1. Returns unmasked phone number and remaining balance.
* **Success Response (200 OK):**
  ```json
  {
    "success": true,
    "statusCode": 200,
    "message": "Contact unlocked successfully",
    "data": {
      "propertyId": "p_101",
      "sellerName": "Rajesh Mehta",
      "unmaskedPhone": "+91 98251 45678",
      "isMasked": false,
      "unlockedContactsRemaining": 39,
      "alreadyUnlocked": false
    }
  }
  ```

---

### 2.7. Professional Services Hub Module (`/api/v1/services`)

#### 24. `GET /api/v1/services/professionals/:category`
* **When Called in App:** `lib/features/services/service_professionals_screen.dart`.
* **Path Params:** `category`: `Interior & Vastu`, `Legal Title Verification`, `Lawyer / Advocate`, `Banker (for Loan)`.
* **Success Response (200 OK):** List of verified certified professionals and newly approved applicants. Phone numbers masked for free users.

#### 25. `POST /api/v1/services/register`
* **When Called in App:** `lib/features/services/service_provider_register_screen.dart`.
* **Request Body:** `name`, `firmName`, `phone`, `city`, `serviceCategory`, `experienceYears`, `specializations`. Status defaults to `Pending`.

#### 26. `GET /api/v1/services/my-requests`
* **When Called in App:** `lib/features/services/my_service_requests_screen.dart`. Returns user's registrations with statuses (`Pending`, `Approved`, `Rejected`).

#### 27. `POST /api/v1/services/request-callback`
* **When Called in App:** On clicking "Request Callback" on any service professional card modal or inside `lib/features/services/home_loan_screen.dart`.
* **Request Body:**
  ```json
  {
    "professionalId": "sp_501",
    "serviceCategory": "Banker (for Loan)",
    "bankName": "HDFC Bank",
    "loanAmountRequested": 4500000,
    "userPhone": "9876543210",
    "notes": "Interested in pre-sanctioned home loan at 8.5% ROI"
  }
  ```
* **Success Response (201 Created):** Inserts inquiry into `service_inquiries` and dispatches notification to expert.

---

### 2.8. Discovery, Notifications, Reports & Feedback Modules

#### 28. `GET /api/v1/discovery/cities`
* **When Called in App:** `lib/features/home/search_city_screen.dart`. Returns popular cities and famous localities (Vesu, Adajan, Pal, Dumas Road).

#### 29. `GET /api/v1/discovery/new-projects`
* **When Called in App:** `lib/features/home/new_projects_screen.dart`. Returns RERA registered mega launches with specs and brochure details.

#### 30. `GET /api/v1/notifications`
* **When Called in App:** `lib/features/home/notifications_screen.dart`. Returns alerts filtered by tab (`All`, `Price Drops`, `Leads`, `Payments`, `System`).

#### 31. `PATCH /api/v1/notifications/mark-all-read`
* **When Called in App:** "Mark All Read" button on `NotificationsScreen`.

#### 32. `DELETE /api/v1/notifications/clear-all`
* **When Called in App:** "Clear All" confirmation on `NotificationsScreen`.

#### 33. `POST /api/v1/moderation/report-property`
* **When Called in App:** `PropertyDetailsScreen` "Report Fraud or Incorrect Details" button. Inserts into `property_reports`.

#### 34. `POST /api/v1/system/app-feedback`
* **When Called in App:** In-app rating modal (`AppUpgradeReviewService.requestAppReview`). Inserts rating (1-5) and feedback into `app_feedback_reviews`.

#### 35. `GET /api/v1/system/config`
* **When Called in App:** On app launch. Returns official helpline (`+91 98765 00000`), support email (`support@propertywala.com`), and legal URLs from `system_config`.

---

## 3. Endpoints Summary Table

| # | HTTP Method | Path | Flutter Screen / Component | Auth Required? | Cache Policy |
| :-: | :--- | :--- | :--- | :-: | :--- |
| **1** | `POST` | `/api/v1/auth/register` | `RegisterScreen` | No | No Cache |
| **2** | `POST` | `/api/v1/auth/login` | `LoginScreen` | No | No Cache |
| **3** | `POST` | `/api/v1/auth/refresh-token` | `ApiClient` Interceptor | Yes (Refresh) | No Cache |
| **4** | `GET` | `/api/v1/auth/profile` | `ProfileScreen` | Yes | 30s Private |
| **5** | `PUT` | `/api/v1/auth/profile` | `EditProfileScreen` | Yes | Invalidate |
| **6** | `POST` | `/api/v1/auth/logout` | `ProfileScreen` Dialog | Yes | Invalidate |
| **7** | `GET` | `/api/v1/properties` | `HomeScreen`, `PropertyListScreen` | Optional | 60s Public |
| **8** | `GET` | `/api/v1/properties/by-code/:code` | Search Bar (`032`) | No | 5m Public |
| **9** | `GET` | `/api/v1/properties/:id` | `PropertyDetailsScreen` | Optional | 30s Dynamic |
| **10** | `POST` | `/api/v1/properties` | `PostPropertyScreen` (Step 3) | Yes | Invalidate |
| **11** | `GET` | `/api/v1/properties/my/listings`| `MyPropertiesScreen` | Yes | No Cache |
| **12** | `DELETE`| `/api/v1/properties/:id` | `MyPropertiesScreen` Delete | Yes | Invalidate |
| **13** | `POST` | `/api/v1/properties/upload-image`| Photo Picker Sheet | Yes | No Cache |
| **14** | `GET` | `/api/v1/bank-auctions` | `BankAuctionScreen` | No | 60s Public |
| **15** | `GET` | `/api/v1/bank-auctions/:id` | `BankAuctionDetailsScreen` | No | 5m Public |
| **16** | `POST` | `/api/v1/leads/contact-seller` | `ContactOwnerSheet` | Yes | Invalidate |
| **17** | `GET` | `/api/v1/leads/property/:id` | `PropertyLeadsScreen` | Yes (Owner) | No Cache |
| **18** | `GET` | `/api/v1/saved` | `SavedScreen` | Yes | 30s Private |
| **19** | `POST` | `/api/v1/saved/toggle/:id` | Heart Icon Toggle | Yes | Invalidate |
| **20** | `GET` | `/api/v1/pro/plans` | `ProMembershipScreen` | No | 1hr Public |
| **21** | `POST` | `/api/v1/pro/create-order` | `ProMembershipScreen` Pay | Yes | No Cache |
| **22** | `POST` | `/api/v1/pro/verify-payment` | Payment Callback | Yes | Invalidate |
| **23** | `POST` | `/api/v1/pro/unlock-contact` | Contact Blur Unmasking | Yes | Invalidate |
| **24** | `GET` | `/api/v1/services/professionals/:category` | `ServiceProfessionalsScreen`| Optional | 5m Public |
| **25** | `POST` | `/api/v1/services/register` | `ServiceProviderRegisterScreen` | Yes | Invalidate |
| **26** | `GET` | `/api/v1/services/my-requests` | `MyServiceRequestsScreen` | Yes | No Cache |
| **27** | `POST` | `/api/v1/services/request-callback` | Request Callback Dialog | Yes | Invalidate |
| **28** | `GET` | `/api/v1/discovery/cities` | `SearchCityScreen` | No | 24hr Redis |
| **29** | `GET` | `/api/v1/discovery/new-projects`| `NewProjectsScreen` | No | 6hr Redis |
| **30** | `GET` | `/api/v1/notifications` | `NotificationsScreen` | Yes | No Cache |
| **31** | `PATCH`| `/api/v1/notifications/mark-all-read`| Notifications Action | Yes | Invalidate |
| **32** | `DELETE`| `/api/v1/notifications/clear-all`| Notifications Action | Yes | Invalidate |
| **33** | `POST` | `/api/v1/moderation/report-property` | Report Listing Dialog | Optional | No Cache |
| **34** | `POST` | `/api/v1/system/app-feedback` | In-App Review Dialog | Optional | No Cache |
| **35** | `GET` | `/api/v1/system/config` | App Startup (`main.dart`) | No | 12hr Redis |

---

---

## 4. Enterprise Admin & Back-Office API Catalog (`/api/v1/admin`)

All Admin endpoints require `Authorization: Bearer <token>` verified by `AdminAuthGuard` with active role permissions.

#### 36. `POST /api/v1/admin/auth/login`
* **Consumer:** Next.js Admin Portal Login (`(auth)/login/page.tsx`).
* **Request Body:** `{ "email": "admin@propertywala.com", "password": "SecurePassword", "totpCode": "123456" }`.
* **Success Response (200 OK):** Returns Admin JWT, role (`SuperAdmin`, `PropertyModerator`, etc.), and permissions array.

#### 37. `GET /api/v1/admin/auth/me`
* **Consumer:** Topbar user profile & permission hydration.
* **Success Response (200 OK):** Current session profile, 2FA status, and assigned granular privileges.

#### 38. `GET /api/v1/admin/analytics/overview`
* **Consumer:** `/admin/dashboard` KPI metric strip.
* **Success Response (200 OK):** Real-time counters: Active properties, pending moderation queue, bank auctions, active Pro members, MRR (₹), and 24h lead volume.

#### 39. `GET /api/v1/admin/analytics/lead-trends`
* **Consumer:** `/admin/dashboard` 30-day velocity chart.
* **Query Params:** `?startDate=2026-10-01&endDate=2026-10-31`.

#### 40. `GET /api/v1/admin/properties`
* **Consumer:** `/admin/properties` Master Listing datatable.
* **Query Params:** `?code=032&status=Under Review&city=Surat&page=1&limit=25`.

#### 41. `GET /api/v1/admin/properties/:id`
* **Consumer:** Property Inspection Sliding Drawer. Returns unmasked seller mobile, all high-res photos, and specification checklist.

#### 42. `PATCH /api/v1/admin/properties/:id/code`
* **Consumer:** Code Assignment Modal (`/admin/properties`).
* **Request Body:** `{ "code": "032" }`. Validates uniqueness across all non-deleted properties.
* **Success Response (200 OK):** `{ "success": true, "code": "032", "message": "Code assigned successfully" }`. Invalidates Redis code lookup cache.
* **Collision Error Response (409 Conflict):**
  ```json
  {
    "success": false,
    "statusCode": 409,
    "message": "Property code '032' is already assigned to 'Sky Terrace, Vesu'",
    "data": {
      "conflictingCode": "032",
      "suggestedNextCode": "033"
    }
  }
  ```

#### 43. `PATCH /api/v1/admin/properties/:id/status`
* **Consumer:** Property Moderation Actions.
* **Request Body:** `{ "status": "Active", "rejectionReason": null }` or `{ "status": "Rejected", "rejectionReason": "Blurry photos" }`.

#### 44. `PATCH /api/v1/admin/properties/:id/verify`
* **Consumer:** Verification toggle. Sets `is_verified = true` to display green 100% Verified badge on the app.

#### 45. `DELETE /api/v1/admin/properties/:id`
* **Consumer:** Permanent property delisting / purge.

#### 46. `GET /api/v1/admin/bank-auctions`
* **Consumer:** `/admin/bank-auctions` Distressed assets & seized vehicles manager.

#### 47. `POST /api/v1/admin/bank-auctions`
* **Consumer:** Create bank auction listing with SARFAESI parameters (Bank name, reserve price, EMD, auction date, inspection yard).

#### 48. `PUT /api/v1/admin/bank-auctions/:id`
* **Consumer:** Update auction parameters, reserve price, or stockyard location.

#### 49. `POST /api/v1/admin/bank-auctions/upload-pdf`
* **Consumer:** Upload official bank auction notice / tender gazette PDF.

#### 50. `GET /api/v1/admin/services`
* **Consumer:** `/admin/service-providers` Verification queue (Interior, Title, Lawyer, Banker).

#### 51. `PATCH /api/v1/admin/services/:id/approval`
* **Consumer:** Approve or reject service provider application with feedback note.

#### 52. `GET /api/v1/admin/services/inquiries`
* **Consumer:** Callback inquiries ledger submitted by mobile users.

#### 53. `GET /api/v1/admin/pro/subscribers`
* **Consumer:** `/admin/pro-memberships` Paid subscribers directory and revenue ledger.

#### 54. `POST /api/v1/admin/pro/grant-bonus`
* **Consumer:** VIP Support modal. Adds bonus contact unlock credits to user's quota.

#### 55. `GET /api/v1/admin/pro/unlock-audit`
* **Consumer:** Live Phone Unmasking Ledger. Inspects which user unmasked which seller phone number.

#### 56. `GET /api/v1/admin/leads`
* **Consumer:** `/admin/leads` Buyer inquiries stream with automated spam detection flags.

#### 57. `POST /api/v1/admin/new-projects`
* **Consumer:** `/admin/new-projects` Create RERA mega project showcase.

#### 58. `PUT /api/v1/admin/new-projects/:id`
* **Consumer:** Update RERA project unit pricing, specifications, and brochure PDF.

#### 59. `GET /api/v1/admin/users`
* **Consumer:** `/admin/users` Master registered user directory.

#### 60. `GET /api/v1/admin/users/:id`
* **Consumer:** User Profile Dossier (Listings posted, leads received, Pro history, device footprint).

#### 61. `PATCH /api/v1/admin/users/:id/ban`
* **Consumer:** Account suspension toggle. `{ "isBanned": true, "reason": "Fraudulent listing" }`.

#### 62. `POST /api/v1/admin/notifications/broadcast`
* **Consumer:** `/admin/notifications` Push Notification Broadcast Studio (Google FCM).

#### 63. `GET /api/v1/admin/locations/cities`
* **Consumer:** `/admin/locations` City master list and coordinates editor.

#### 64. `POST /api/v1/admin/locations/localities`
* **Consumer:** Add famous locality under a city with price-per-sqft guidance.

#### 65. `GET /api/v1/admin/reports`
* **Consumer:** `/admin/reports` Community fraud moderation queue.

#### 66. `PATCH /api/v1/admin/reports/:id/resolve`
* **Consumer:** Resolve fraud report: `{ "action": "DELIST", "resolutionNotes": "Verified duplicate" }`.

#### 67. `GET /api/v1/admin/feedback`
* **Consumer:** `/admin/feedback` In-app user ratings (1-5 stars) & review stream.

#### 68. `GET /api/v1/admin/settings`
* **Consumer:** `/admin/settings` Read dynamic system configuration.

#### 69. `PUT /api/v1/admin/settings`
* **Consumer:** Update helpline phone, support email, legal URLs, and maintenance mode.

#### 70. `GET /api/v1/admin/audit-logs`
* **Consumer:** `/admin/audit-logs` Immutable audit trail with before vs after JSON diffs.

---

## 5. Admin & Back-Office Endpoints Summary Table (36 to 70)

| # | HTTP Method | Path | Admin Module | Role Access | Function |
| :-: | :--- | :--- | :--- | :--- | :--- |
| **36**| `POST` | `/api/v1/admin/auth/login` | Login | All Admins | Admin Auth + 2FA |
| **37**| `GET` | `/api/v1/admin/auth/me` | Topbar | All Admins | Session Identity |
| **38**| `GET` | `/api/v1/admin/analytics/overview` | Dashboard | SuperAdmin, Auditor | KPI Summary & MRR |
| **39**| `GET` | `/api/v1/admin/analytics/lead-trends`| Dashboard | SuperAdmin, Auditor | 30-Day Velocity |
| **40**| `GET` | `/api/v1/admin/properties` | Properties | Moderator, SuperAdmin| Filtered Listings Grid |
| **41**| `GET` | `/api/v1/admin/properties/:id` | Properties | Moderator, SuperAdmin| Full Inspection Drawer |
| **42**| `PATCH`| `/api/v1/admin/properties/:id/code` | Properties | Moderator, SuperAdmin| **Assign Code ('032')** |
| **43**| `PATCH`| `/api/v1/admin/properties/:id/status`| Properties | Moderator, SuperAdmin| Approve / Reject |
| **44**| `PATCH`| `/api/v1/admin/properties/:id/verify`| Properties | Moderator, SuperAdmin| 100% Verified Badge |
| **45**| `DELETE`| `/api/v1/admin/properties/:id` | Properties | SuperAdmin | Hard Remove Listing |
| **46**| `GET` | `/api/v1/admin/bank-auctions` | Bank Auctions| AuctionOfficer, SuperAdmin| Distressed Assets Grid |
| **47**| `POST` | `/api/v1/admin/bank-auctions` | Bank Auctions| AuctionOfficer, SuperAdmin| Create SARFAESI Asset |
| **48**| `PUT` | `/api/v1/admin/bank-auctions/:id` | Bank Auctions| AuctionOfficer, SuperAdmin| Update Tender/Stockyard |
| **49**| `POST` | `/api/v1/admin/bank-auctions/upload-pdf`| Bank Auctions| AuctionOfficer, SuperAdmin| Upload Notice PDF |
| **50**| `GET` | `/api/v1/admin/services` | Service Pros | ServiceApprover, SuperAdmin| Application Queue |
| **51**| `PATCH`| `/api/v1/admin/services/:id/approval`| Service Pros | ServiceApprover, SuperAdmin| Approve / Reject Expert |
| **52**| `GET` | `/api/v1/admin/services/inquiries` | Service Pros | ServiceApprover, SuperAdmin| Callback Inquiries |
| **53**| `GET` | `/api/v1/admin/pro/subscribers` | Pro Members | SuperAdmin, Support | Subscribers Directory |
| **54**| `POST` | `/api/v1/admin/pro/grant-bonus` | Pro Members | SuperAdmin | Bonus Unlock Credits |
| **55**| `GET` | `/api/v1/admin/pro/unlock-audit` | Pro Members | SuperAdmin, Moderator | Unmasking Security Audit|
| **56**| `GET` | `/api/v1/admin/leads` | Leads Stream | Support, Moderator, SuperAdmin| Leads & Spam Inspector |
| **57**| `POST` | `/api/v1/admin/new-projects` | Mega Projects| Moderator, SuperAdmin | Add Luxury Showcase |
| **58**| `PUT` | `/api/v1/admin/new-projects/:id` | Mega Projects| Moderator, SuperAdmin | Update Units / Brochure |
| **59**| `GET` | `/api/v1/admin/users` | Users | SuperAdmin, Support | Registered Users Master |
| **60**| `GET` | `/api/v1/admin/users/:id` | Users | SuperAdmin, Support | User Dossier & Footprint|
| **61**| `PATCH`| `/api/v1/admin/users/:id/ban` | Users | SuperAdmin | Suspend / Unban User |
| **62**| `POST` | `/api/v1/admin/notifications/broadcast`| Broadcast | SuperAdmin | FCM Push Campaigns |
| **63**| `GET` | `/api/v1/admin/locations/cities` | Locations | Moderator, SuperAdmin | Cities & Coordinates |
| **64**| `POST` | `/api/v1/admin/locations/localities`| Locations | Moderator, SuperAdmin | Add Famous Locality |
| **65**| `GET` | `/api/v1/admin/reports` | Fraud Reports| Moderator, SuperAdmin | Community Flag Triage |
| **66**| `PATCH`| `/api/v1/admin/reports/:id/resolve` | Fraud Reports| Moderator, SuperAdmin | Delist or Dismiss |
| **67**| `GET` | `/api/v1/admin/feedback` | Feedback | Support, SuperAdmin | Ratings (1-5) & VOC |
| **68**| `GET` | `/api/v1/admin/settings` | Settings | All Staff | Read Config |
| **69**| `PUT` | `/api/v1/admin/settings` | Settings | SuperAdmin | Update Helpline / URLs |
| **70**| `GET` | `/api/v1/admin/audit-logs` | Audit Logs | All Admins (Role Filter)| Immutable Change Trail |

---

## 6. Cross-System Architecture Traceability Matrix

| Flutter Screen / Action | REST API Endpoint | PostgreSQL Table | Admin Panel Module |
| :--- | :--- | :--- | :--- |
| `Search Bar ('032')` | `GET /api/v1/properties/by-code/:code` | `properties.property_code` | `/admin/properties` (Code Assign) |
| `PostPropertyScreen` (3 Steps) | `POST /api/v1/properties` | `properties`, `property_images` | `/admin/properties` (Review Queue) |
| `BankAuctionScreen` | `GET /api/v1/bank-auctions` | `properties` (`is_bank_auction = true`) | `/admin/bank-auctions` |
| `ContactOwnerSheet` (Unmask) | `POST /api/v1/pro/unlock-contact` | `pro_contact_unlocks`, `pro_memberships`| `/admin/pro-memberships/unlock-audit` |
| `ContactOwnerSheet` (Inquiry)| `POST /api/v1/leads/contact-seller`| `buyer_leads` | `/admin/leads` |
| `SavedScreen` (Heart Icon) | `POST /api/v1/saved/toggle/:id` | `saved_properties` | Read-only analytics |
| `ServiceProfessionalsScreen`| `GET /api/v1/services/professionals/:cat`| `service_professionals` | `/admin/service-providers` |
| `NewProjectsScreen` | `GET /api/v1/discovery/new-projects`| `new_projects`, `project_units` | `/admin/new-projects` |
| `SearchCityScreen` | `GET /api/v1/discovery/cities` | `cities`, `famous_localities` | `/admin/locations` |
| `HelpSupportScreen` | `GET /api/v1/system/config` | `system_config` | `/admin/settings` |
| Report Listing Dialog | `POST /api/v1/moderation/report-property`| `property_reports` | `/admin/reports` |
| App Rating Dialog | `POST /api/v1/system/app-feedback` | `app_feedback_reviews` | `/admin/feedback` |

---

## 7. Production Readiness Verification
Every single user action, button click, search query, filter permutation, payment flow, and administrative moderation capability across both the Propertywala Flutter Client and Next.js Admin Panel is **100% matched with a designated REST endpoint and PostgreSQL table**. There are zero missing APIs.
