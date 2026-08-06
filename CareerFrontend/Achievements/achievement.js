

      const API_BASE_URL = "http://127.0.0.1:8000";

      const ENDPOINTS = {
        all: `${API_BASE_URL}/api/user/achievements`,
        earned: `${API_BASE_URL}/api/user/achievements/earned`,
        check: `${API_BASE_URL}/api/user/achievements/check`,
        dashboard: `${API_BASE_URL}/api/user/dashboard`,
      };

      /* =========================================
         DOM
      ========================================== */

      const sidebarMenu = document.getElementById("sidebarMenu");
      const menuTemplate = document.getElementById("menuTemplate");
      const summaryContainer = document.getElementById("summaryContainer");
      const summaryTemplate = document.getElementById("summaryTemplate");
      const achievementContainer = document.getElementById(
        "achievementContainer",
      );
      const achievementTemplate = document.getElementById(
        "achievementTemplate",
      );
      const searchInput = document.getElementById("searchInput");
      const filterGroup = document.getElementById("filterGroup");
      const resultText = document.getElementById("resultText");
      const loading = document.getElementById("loading");
      const toast = document.getElementById("toast");
      const refreshBtn = document.getElementById("refreshBtn");
      const checkBtn = document.getElementById("checkBtn");
      const notificationBtn = document.getElementById("notificationBtn");

      /* =========================================
         STATE
      ========================================== */

      let achievements = [];
      let currentFilter = "all";
      let searchKeyword = "";

      const menus = [
        {
          icon: "fa-house",
          title: "Dashboard",
          link: "dashboard.html",
        },
        {
          icon: "fa-map",
          title: "Roadmaps",
          link: "roadmaps.html",
        },
        {
          icon: "fa-chart-column",
          title: "Progress",
          link: "progress.html",
        },
        {
          icon: "fa-trophy",
          title: "Achievements",
          link: "achievements.html",
          active: true,
        },
        {
          icon: "fa-user",
          title: "Profile",
          link: "profile.html",
        },
      ];

      /* =========================================
         SIDEBAR
      ========================================== */

      function renderSidebar() {
        sidebarMenu.innerHTML = "";

        menus.forEach((menu) => {
          const template = menuTemplate.content.cloneNode(true);
          const link = template.querySelector("a");
          const icon = template.querySelector("i");
          const text = template.querySelector("span");

          icon.className = `fa-solid ${menu.icon}`;
          text.textContent = menu.title;
          link.href = menu.link || "#";

          if (menu.active) {
            link.classList.add("active");
          }

          link.addEventListener("click", () => {
            document
              .querySelectorAll("#sidebarMenu a")
              .forEach((item) => item.classList.remove("active"));
            link.classList.add("active");
          });

          sidebarMenu.appendChild(template);
        });
      }

      function loadProfileFromCache() {
        const possibleKeys = ["career_dashboard", "user", "auth_user"];
        let user = null;

        for (const key of possibleKeys) {
          try {
            const value = localStorage.getItem(key);
            if (!value) continue;

            const parsed = JSON.parse(value);
            user = parsed?.user || parsed;

            if (user?.name) break;
          } catch {
            // Ignore invalid cache entries.
          }
        }

        if (!user) return;

        document.getElementById("userName").textContent = user.name || "User";
        document.getElementById("userRole").textContent = capitalize(
          user.role || "member",
        );

        const avatar = user.avatar || user.profile_image || user.image;
        if (avatar) {
          document.getElementById("sidebarAvatar").src = avatar;
        }
      }

      /* =========================================
         UI HELPERS
      ========================================== */

      function showLoading() {
        loading.classList.add("show");
      }

      function hideLoading() {
        loading.classList.remove("show");
      }

      function showToast(message, type = "info") {
        const colors = {
          info: "#4f46e5",
          success: "#22c55e",
          error: "#ef4444",
        };

        toast.textContent = message;
        toast.style.background = colors[type] || colors.info;
        toast.classList.add("show");

        window.clearTimeout(showToast.timer);
        showToast.timer = window.setTimeout(() => {
          toast.classList.remove("show");
        }, 3000);
      }

      function capitalize(value) {
        if (!value) return "";
        return value.charAt(0).toUpperCase() + value.slice(1);
      }

      function clamp(value, min = 0, max = 100) {
        const number = Number(value) || 0;
        return Math.min(max, Math.max(min, number));
      }

      function formatDate(value) {
        if (!value) return "";

        const date = new Date(value);
        if (Number.isNaN(date.getTime())) return String(value);

        return date.toLocaleDateString(undefined, {
          year: "numeric",
          month: "short",
          day: "numeric",
        });
      }

      function getAuthToken() {
        return (
          localStorage.getItem("token") ||
          localStorage.getItem("access_token") ||
          localStorage.getItem("authToken")
        );
      }

      /* =========================================
         HTTP
      ========================================== */

      async function apiRequest(url, options = {}) {
        const token = getAuthToken();

        if (!token) {
          throw new Error("Login token not found. Please log in again.");
        }

        const headers = {
          Accept: "application/json",
          ...(options.body ? { "Content-Type": "application/json" } : {}),
          Authorization: `Bearer ${token}`,
          ...(options.headers || {}),
        };

        let response;

        try {
          response = await fetch(url, {
            ...options,
            headers,
          });
        } catch {
          throw new Error(
            "Failed to connect to Laravel. Check the server URL and CORS settings.",
          );
        }

        const contentType = response.headers.get("content-type") || "";
        let payload = null;

        if (contentType.includes("application/json")) {
          payload = await response.json();
        } else {
          const text = await response.text();
          payload = text ? { message: text } : null;
        }

        if (!response.ok) {
          if (response.status === 401) {
            throw new Error("Your login session has expired. Please log in again.");
          }

          throw new Error(
            payload?.message ||
              payload?.error ||
              `Request failed with status ${response.status}.`,
          );
        }

        return payload;
      }

      /* =========================================
         NORMALIZE API DATA
      ========================================== */

      function findArray(payload, preferredKeys = []) {
        if (Array.isArray(payload)) return payload;

        for (const key of preferredKeys) {
          const direct = payload?.[key];
          if (Array.isArray(direct)) return direct;

          const nested = payload?.data?.[key];
          if (Array.isArray(nested)) return nested;
        }

        if (Array.isArray(payload?.data)) return payload.data;
        if (Array.isArray(payload?.results)) return payload.results;

        return [];
      }

      function getProgressValue(item) {
        const raw =
          item.progress_percentage ??
          item.progress ??
          item.completion_percentage ??
          item.user_progress?.percentage ??
          item.user_progress?.progress ??
          0;

        if (typeof raw === "object" && raw !== null) {
          return clamp(
            raw.percentage ?? raw.progress ?? raw.value ?? raw.current ?? 0,
          );
        }

        return clamp(raw);
      }

      function normalizeAchievement(item, index, earnedIds = new Set()) {
        const pivot = item.pivot || item.user_achievement || {};
        const id = item.id ?? item.achievement_id ?? index + 1;
        const status = String(
          item.status ?? pivot.status ?? item.achievement_status ?? "",
        ).toLowerCase();

        const explicitEarned =
          item.earned ??
          item.is_earned ??
          item.unlocked ??
          item.is_unlocked;

        const earned =
          explicitEarned !== undefined && explicitEarned !== null
            ? Boolean(explicitEarned)
            : Boolean(
                pivot.earned_at ||
                  pivot.completed_at ||
                  earnedIds.has(String(id)) ||
                  ["earned", "completed", "unlocked"].includes(status),
              );

        const progress = earned ? 100 : getProgressValue(item);
        const target = Number(
          item.target ??
            item.target_value ??
            item.requirement_value ??
            item.criteria_value ??
            100,
        );
        const current = Number(
          item.current ??
            item.current_value ??
            item.progress_value ??
            item.user_progress?.current ??
            Math.round((progress / 100) * target),
        );

        return {
          id,
          title:
            item.name ??
            item.title ??
            item.achievement_name ??
            `Achievement ${index + 1}`,
          description:
            item.description ??
            item.details ??
            item.summary ??
            "Complete learning activities to unlock this achievement.",
          criteria:
            item.criteria ??
            item.requirement ??
            item.condition ??
            item.criteria_description ??
            "Complete the required learning activity.",
          icon: item.icon || item.icon_class || "fa-trophy",
          points: Number(item.points ?? item.point ?? item.reward_points ?? 0),
          earned,
          progress,
          current,
          target: target || 100,
          earnedAt:
            item.earned_at ??
            item.unlocked_at ??
            item.completed_at ??
            pivot.earned_at ??
            pivot.created_at ??
            null,
        };
      }

      function mergeAchievements(allPayload, earnedPayload) {
        const allList = findArray(allPayload, [
          "achievements",
          "all_achievements",
          "items",
        ]);
        const earnedList = findArray(earnedPayload, [
          "achievements",
          "earned",
          "earned_achievements",
          "items",
        ]);

        const earnedIds = new Set(
          earnedList.map((item) =>
            String(item.id ?? item.achievement_id ?? item.pivot?.achievement_id),
          ),
        );

        const normalizedAll = allList.map((item, index) =>
          normalizeAchievement(item, index, earnedIds),
        );

        const knownIds = new Set(normalizedAll.map((item) => String(item.id)));

        earnedList.forEach((item, index) => {
          const id = String(
            item.id ?? item.achievement_id ?? item.pivot?.achievement_id,
          );

          if (!knownIds.has(id)) {
            normalizedAll.push(
              normalizeAchievement(
                {
                  ...item,
                  earned: true,
                },
                normalizedAll.length + index,
                earnedIds,
              ),
            );
          }
        });

        return normalizedAll;
      }

      /* =========================================
         API SERVICE
      ========================================== */

      const AchievementAPI = {
        async getAll() {
          return apiRequest(ENDPOINTS.all);
        },

        async getEarned() {
          return apiRequest(ENDPOINTS.earned);
        },

        async check() {
          return apiRequest(ENDPOINTS.check, {
            method: "POST",
          });
        },
      };

      /* =========================================
         RENDER SUMMARY
      ========================================== */

      function renderSummary() {
        const earned = achievements.filter((item) => item.earned);
        const locked = achievements.length - earned.length;
        const points = earned.reduce((sum, item) => sum + item.points, 0);
        const completion = achievements.length
          ? Math.round((earned.length / achievements.length) * 100)
          : 0;

        const summary = [
          {
            title: "Earned Badges",
            value: earned.length,
            description: `${achievements.length} total achievements`,
            icon: "fa-trophy",
          },
          {
            title: "Total Points",
            value: points,
            description: "Points collected from earned badges",
            icon: "fa-star",
          },
          {
            title: "Locked",
            value: locked,
            description: "Achievements still available to unlock",
            icon: "fa-lock",
          },
          {
            title: "Completion",
            value: `${completion}%`,
            description: "Overall achievement collection progress",
            icon: "fa-chart-pie",
          },
        ];

        summaryContainer.innerHTML = "";

        summary.forEach((item) => {
          const template = summaryTemplate.content.cloneNode(true);
          template.querySelector(".summary-icon i").className =
            `fa-solid ${item.icon}`;
          template.querySelector("h4").textContent = item.title;
          template.querySelector("h2").textContent = item.value;
          template.querySelector("p").textContent = item.description;
          summaryContainer.appendChild(template);
        });
      }

      function renderFeatured() {
        const earned = achievements
          .filter((item) => item.earned)
          .sort((a, b) => {
            const first = a.earnedAt ? new Date(a.earnedAt).getTime() : 0;
            const second = b.earnedAt ? new Date(b.earnedAt).getTime() : 0;
            return second - first;
          });

        const latest = earned[0];
        const title = document.getElementById("featuredTitle");
        const description = document.getElementById("featuredDescription");
        const points = document.getElementById("featuredPoints");
        const icon = document.querySelector(".featured-icon i");

        if (!latest) {
          title.textContent = "No achievement earned yet";
          description.textContent =
            "Complete roadmap tasks to unlock your first achievement.";
          points.textContent = "0";
          icon.className = "fa-solid fa-trophy";
          return;
        }

        title.textContent = latest.title;
        description.textContent = latest.description;
        points.textContent = latest.points;
        icon.className = `fa-solid ${latest.icon}`;
      }

      /* =========================================
         RENDER ACHIEVEMENTS
      ========================================== */

      function getVisibleAchievements() {
        return achievements.filter((item) => {
          const matchesFilter =
            currentFilter === "all" ||
            (currentFilter === "earned" && item.earned) ||
            (currentFilter === "locked" && !item.earned);

          const searchable = `${item.title} ${item.description} ${item.criteria}`
            .toLowerCase();
          const matchesSearch = searchable.includes(searchKeyword);

          return matchesFilter && matchesSearch;
        });
      }

      function renderAchievements() {
        achievementContainer.innerHTML = "";

        const visible = getVisibleAchievements();
        resultText.textContent = `${visible.length} of ${achievements.length} achievement${
          achievements.length === 1 ? "" : "s"
        } shown`;

        if (!visible.length) {
          achievementContainer.innerHTML = `
            <div class="empty-state">
              <i class="fa-solid fa-medal"></i>
              <h3>No achievements found</h3>
              <p>Try another search keyword or change the selected filter.</p>
            </div>
          `;
          return;
        }

        visible.forEach((achievement) => {
          const template = achievementTemplate.content.cloneNode(true);
          const card = template.querySelector(".achievement-card");
          const icon = template.querySelector(".achievement-icon i");
          const badge = template.querySelector(".status-badge");
          const title = template.querySelector(".achievement-title");
          const description = template.querySelector(
            ".achievement-description",
          );
          const criteria = template.querySelector(".criteria-box");
          const progressFill = template.querySelector(".progress-fill");
          const progressText = template.querySelector(".progress-text");
          const progressPercent = template.querySelector(".progress-percent");
          const pointValue = template.querySelector(".point-value");
          const earnedDate = template.querySelector(".earned-date");

          card.classList.add(achievement.earned ? "earned" : "locked");
          icon.className = `fa-solid ${
            achievement.earned ? achievement.icon : "fa-lock"
          }`;

          badge.textContent = achievement.earned ? "Earned" : "Locked";
          badge.classList.add(achievement.earned ? "earned" : "locked");

          title.textContent = achievement.title;
          description.textContent = achievement.description;
          criteria.innerHTML = "";

          const criteriaLabel = document.createElement("strong");
          criteriaLabel.textContent = "Requirement: ";
          criteria.append(criteriaLabel, achievement.criteria);

          progressFill.style.width = `${achievement.progress}%`;
          progressText.textContent = achievement.earned
            ? "Completed"
            : `${achievement.current}/${achievement.target}`;
          progressPercent.textContent = `${achievement.progress}%`;
          pointValue.textContent = `${achievement.points} points`;
          earnedDate.textContent = achievement.earned
            ? achievement.earnedAt
              ? `Earned ${formatDate(achievement.earnedAt)}`
              : "Achievement earned"
            : "Not earned yet";

          achievementContainer.appendChild(template);
        });
      }

      function renderPage() {
        renderSummary();
        renderFeatured();
        renderAchievements();
      }

      /* =========================================
         LOAD DATA
      ========================================== */

      function saveCache() {
        try {
          localStorage.setItem(
            "career_achievements_cache",
            JSON.stringify(achievements),
          );
        } catch {
          // Storage can fail in private mode; the page still works.
        }
      }

      function loadCache() {
        try {
          const cached = localStorage.getItem("career_achievements_cache");
          return cached ? JSON.parse(cached) : [];
        } catch {
          return [];
        }
      }

      async function loadAchievements(showFullLoader = true) {
        if (showFullLoader) showLoading();

        try {
          const [allPayload, earnedPayload] = await Promise.all([
            AchievementAPI.getAll(),
            AchievementAPI.getEarned(),
          ]);

          achievements = mergeAchievements(allPayload, earnedPayload);
          saveCache();
          renderPage();
        } catch (error) {
          console.error("Achievement API error:", error);

          const cached = loadCache();

          if (cached.length) {
            achievements = cached;
            renderPage();
            showToast("API unavailable. Showing saved achievements.", "info");
          } else {
            achievementContainer.innerHTML = `
              <div class="empty-state">
                <i class="fa-solid fa-circle-exclamation"></i>
                <h3>Unable to load achievements</h3>
                <p></p>
              </div>
            `;
            achievementContainer.querySelector("p").textContent =
              error.message;
            resultText.textContent = "Achievements could not be loaded.";
            showToast(error.message, "error");
          }
        } finally {
          if (showFullLoader) hideLoading();
        }
      }

      async function checkNewAchievements() {
        checkBtn.disabled = true;
        checkBtn.innerHTML = `
          <i class="fa-solid fa-spinner fa-spin"></i>
          <span>Checking</span>
        `;

        try {
          const result = await AchievementAPI.check();
          const message =
            result?.message ||
            result?.data?.message ||
            "Achievement check completed.";

          showToast(message, "success");
          await loadAchievements(false);
        } catch (error) {
          showToast(error.message, "error");
        } finally {
          checkBtn.disabled = false;
          checkBtn.innerHTML = `
            <i class="fa-solid fa-wand-magic-sparkles"></i>
            <span>Check New</span>
          `;
        }
      }

      /* =========================================
         EVENTS
      ========================================== */

      searchInput.addEventListener("input", function () {
        searchKeyword = this.value.trim().toLowerCase();
        renderAchievements();
      });

      filterGroup.addEventListener("click", (event) => {
        const button = event.target.closest(".filter-btn");
        if (!button) return;

        currentFilter = button.dataset.filter || "all";

        filterGroup
          .querySelectorAll(".filter-btn")
          .forEach((item) => item.classList.remove("active"));
        button.classList.add("active");

        renderAchievements();
      });

      refreshBtn.addEventListener("click", () => loadAchievements(true));
      checkBtn.addEventListener("click", checkNewAchievements);

      notificationBtn.addEventListener("click", () => {
        showToast("Open the notifications page to view new updates.", "info");
      });

      document.addEventListener("keydown", (event) => {
        if (event.key === "/" && document.activeElement !== searchInput) {
          event.preventDefault();
          searchInput.focus();
        }
      });

      /* =========================================
         START
      ========================================== */

      document.addEventListener("DOMContentLoaded", () => {
        renderSidebar();
        loadProfileFromCache();
        loadAchievements(true);
      });
