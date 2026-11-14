# Full-Stack Integration & Smooth App Performance Guide
**Project:** Propertywala App (Real Estate & Bank Seized Assets Platform)  
**Role:** Principal Mobile & Backend Solutions Architect (10+ Years Experience)  
**Objective:** Seamless Flutter App <-> Backend API <-> Database Integration with Zero Lag

---

## 1. Architectural Integration Flow

```
+-----------------------------------------------------------------------------------+
|                           FLUTTER MOBILE CLIENT (APP)                             |
|                                                                                   |
|  Presentation: HomeScreen | PropertyList | BankAuctions | SavedScreen | Profile    |
|       ^                                    ^                                      |
|       |                                    |                                      |
|  State Stores: PropertyStore | SavedStore | SessionService | ServiceApprovalStore |
|       ^                                                                           |
|       | (Clean Decoupled Bridge)                                                  |
|  Repositories: PropertyRepository | AuthRepository | LeadsRepository | ProRepo    |
|       ^                                                                           |
|       | (Interceptors, Token Refresh, Error Handling)                             |
|  Core Network: ApiClient (Dio / Http) + TokenStorage (Secure Storage)             |
+----------------------------------+------------------------------------------------+
                                   | HTTPS REST JSON / WSS
+----------------------------------v------------------------------------------------+
|                           BACKEND SERVICE ENGINE                                  |
|                                                                                   |
|  API Gateway / Rate Limiting (Express / NestJS)                                   |
|  Authentication Middleware (JWT Access/Refresh, Role Claims)                      |
|  Business Logic Controllers: Properties | BankAuctions | ProEngine | Services     |
|  Phone Number Masking & Access Control Filter (Non-Pro Blur Enforcement)         |
+----------------------------------+------------------------------------------------+
                                   | SQL Queries / Cache Hits
+----------------------------------v------------------------------------------------+
|                        DATA & CACHE INFRASTRUCTURE                                |
|                                                                                   |
|  PostgreSQL 15+ (Primary Storage: Properties, Leads, Codes '032', Subscriptions) |
|  Redis (Cache: Hot Property Feeds, City Localities, Rate Limits, Active Sessions) |
|  Object Storage (AWS S3 / Cloudinary: Compressed WebP Property Photos, Thumbnails)|
+-----------------------------------------------------------------------------------+
```

---

## 2. Step-by-Step Flutter Client Integration Pattern

To transition the current mock stores (`PropertyStore`, `SavedStore`, `SessionService`, `ServiceApprovalStore`) to live backend APIs **without rewriting UI widgets**:

### Step 1: Install Core Flutter Networking Packages
In `pubspec.yaml`:
```yaml
dependencies:
  dio: ^5.4.0                 # High-performance HTTP client with interceptors
  flutter_secure_storage: ^9.0.0 # Encrypted hardware-backed storage for JWT
  connectivity_plus: ^5.0.2   # Instant network state listener
```

### Step 2: Implement Centralized `ApiClient` with Auto-Refresh Token
Create `lib/core/api/api_client.dart`:
```dart
class ApiClient {
  static final Dio dio = Dio(
    BaseOptions(
      baseUrl: "https://api.propertywala.com/api/v1",
      connectTimeout: const Duration(seconds: 10),
      receiveTimeout: const Duration(seconds: 10),
      headers: {"Content-Type": "application/json"},
    ),
  )..interceptors.add(
      InterceptorsWrapper(
        onRequest: (options, handler) async {
          final token = await TokenStorage.getAccessToken();
          if (token != null) {
            options.headers["Authorization"] = "Bearer $token";
          }
          return handler.next(options);
        },
        onError: (DioException error, handler) async {
          if (error.response?.statusCode == 401) {
            // Auto-refresh token routine
            final refreshed = await TokenStorage.refreshTokens();
            if (refreshed) {
              return handler.resolve(await dio.fetch(error.requestOptions));
            }
          }
          return handler.next(error);
        },
      ),
    );
}
```

### Step 3: Connect Existing Stores to Repositories
The existing `ValueNotifier` stores in the Flutter app (`PropertyStore`, `SavedStore`) are already observable. We simply inject the repository calls:

```dart
// Example: Connecting PropertyStore to Live API
class PropertyStore {
  // Existing notifier remains untouched for UI components
  final ValueNotifier<List<PropertyListingItem>> listingsNotifier = ValueNotifier([]);

  Future<void> fetchMyProperties() async {
    final response = await ApiClient.dio.get("/properties/my/listings");
    final List list = response.data["data"];
    listingsNotifier.value = list.map((json) => PropertyListingItem.fromJson(json)).toList();
  }

  Future<void> addProperty({...}) async {
    final response = await ApiClient.dio.post("/properties", data: payload);
    final newItem = PropertyListingItem.fromJson(response.data["data"]);
    listingsNotifier.value = [newItem, ...listingsNotifier.value];
  }
}
```

---

## 3. Techniques for Zero Lag & Ultra-Smooth UX (<60fps)

1. **Skeleton Shimmer Loading Indicators**:
   - Never show a blank white screen or raw blocking spinner. Use existing `SkeletonLoadingCard` while API data streams in.
2. **Offline-First Optimistic UI Updates**:
   - When a user taps the Heart (❤️) to shortlist a property, toggle the `SavedStore` immediately in memory (0ms visual response) and trigger the API call in the background. If the API fails, gracefully roll back and notify via `ToastService`.
3. **Smart Image Caching with Memory Limits**:
   - Utilize our established `AppImageCacheManager` (120MB memory limit) with cached network images so pre-loaded home thumbnails are reused instantly without network redownloading.
4. **Debounced Live Search (250ms)**:
   - For Area/Locality and Unique Property Code (`032`) searches, use a 250ms debounce timer to prevent redundant API spam on every keystroke.
5. **Payload Compression (Gzip / Brotli)**:
   - Enable Gzip / Brotli compression on the backend to shrink 50-item property lists from 80KB to ~12KB.

---

## 4. End-to-End Checklist for Launch Readiness

- [ ] **Database Setup**: Run PostgreSQL migration scripts for all 8 core tables (`db_architecture.md`).
- [ ] **Seed Initial Data**: Seed Gujarat/Surat famous localities, default FAQ support entries, and initial sample properties.
- [ ] **Configure Storage**: Setup AWS S3 / Cloudinary bucket for property and vehicle image uploads with automatic WebP conversion.
- [ ] **Deploy Backend API**: Deploy NestJS / Node.js backend on production server (Railway / AWS ECS / DigitalOcean) with SSL HTTPS certificate.
- [ ] **Deploy Admin Dashboard**: Host Next.js Admin Panel with role-based login to moderate listings and assign codes (`032`).
- [ ] **Final Mobile App Build**: Point Flutter `baseUrl` to live production domain, run release build (`flutter build apk --release`), and publish to Google Play Store.

