      console.log("this is dashboard");
      const API = {
        BASE_URL: "http://127.0.0.1:8000/api/user",
        TOKEN: localStorage.getItem("token"),
      };
      const sidebarMenu = document.getElementById("sidebarMenu");
      const statsContainer = document.getElementById("statsContainer");
      const taskList = document.getElementById("taskList");
      const achievementContainer = document.getElementById(
        "achievementContainer",
      );
      const loading = document.getElementById("loading");
      const toast = document.getElementById("toast");
      const searchInput = document.getElementById("searchInput");
      const welcomeTitle = document.getElementById("welcomeTitle");
      const welcomeText = document.getElementById("welcomeText");
      const dailyTip = document.getElementById("dailyTip");
      const notifBadge = document.getElementById("notifBadge");
      const activityEmpty = document.getElementById("activityEmpty");
      const chartRange = document.getElementById("chartRange");

      
      const menuTemplate = document.getElementById("menuTemplate");
      const statTemplate = document.getElementById("statTemplate");
      const taskTemplate = document.getElementById("taskTemplate");
      const achievementTemplate = document.getElementById(
        "achievementTemplate",
      );

     
      let dashboard = null;
      let tasks = [];
      let recentAchievements = [];
      let stats = [];
      let notifications = [];

      const menus = [
        {
          icon: "fa-house",
          title: "Dashboard",
          link: "dashboard.html",
          active: true,
        },
        { icon: "fa-map", title: "Roadmaps", link: "roadmaps.html" },
        { icon: "fa-chart-column", title: "Progress", link: "progress.html" },
        { icon: "fa-trophy", title: "Achievements", link: "achievements.html" },
        { icon: "fa-user", title: "Profile", link: "profile.html" },
      ];

      
      function showLoading() {
        loading.classList.add("show");
      }

      function hideLoading() {
        loading.classList.remove("show");
      }

      
      function showToast(message) {
        toast.innerHTML = message;
        toast.classList.add("show");
        setTimeout(() => {
          toast.classList.remove("show");
        }, 3000);
      }

     
      function headers() {
        return {
          Accept: "application/json",
          "Content-Type": "application/json",
          Authorization: "Bearer " + API.TOKEN,
        };
      }

     
      async function request(url, options = {}) {
        const response = await fetch(url, {
          ...options,
          headers: headers(),
        });

        if (!response.ok) {
          throw new Error("API Error: " + response.status);
        }

        return await response.json();
      }

      
      async function getDashboard() {
        return await request(API.BASE_URL + "/dashboard");
      }

      async function initializeDashboard() {
        try {
          showLoading();
          const response = await getDashboard();
          dashboard = response;
          console.log(response.notifications);
          prepareDashboard(response);
          saveDashboard();
        } catch (error) {
          console.log(error);
          showToast("Unable to load dashboard.");
        } finally {
          hideLoading();
        }
      }

    
      function capitalize(str) {
        if (!str) return "";
        return str.charAt(0).toUpperCase() + str.slice(1);
      }

      function firstName(name) {
        if (!name) return "there";
        return name.split(" ")[0];
      }

      function clampPct(value) {
        const n = Number(value) || 0;
        return Math.max(0, Math.min(100, n));
      }

     
      function prepareDashboard(data) {
        const user = data.user || {};
        const summary = data.summary || {};
        const roadmaps = data.roadmaps || { total: 0, roadmaps: [] };
        const achievements = data.achievements || {
          total: 0,
          total_points: 0,
          achievements: [],
        };

        document.getElementById("userName").textContent = user.name || "—";
        document.getElementById("userRole").textContent = capitalize(
          user.role || "member",
        );

        welcomeTitle.textContent = `Welcome Back, ${firstName(user.name)}`;

        welcomeText.textContent =
          summary.roadmaps_started > 0
            ? "Continue building your career roadmap."
            : "Start your first roadmap to begin tracking your progress.";

        dailyTip.textContent = "Embrace continuous learning.";

        const totalTasks = summary.total_tasks || 0;
        const completedTasks = summary.completed_tasks || 0;
        const longestStreak = summary.longest_streak || 0;
        const currentStreak = summary.current_streak || 0;

        stats = [
          {
            title: "Overall Progress",
            value: clampPct(summary.overall_progress) + "%",
            sub: `${roadmaps.total || 0} roadmap${
              roadmaps.total === 1 ? "" : "s"
            } in progress`,
            progress: clampPct(summary.overall_progress),
            icon: "fa-chart-line",
          },
          {
            title: "Tasks Completed",
            value: `${completedTasks}/${totalTasks}`,
            sub: totalTasks ? "Keep the momentum going" : "No tasks yet",
            progress: totalTasks
              ? clampPct((completedTasks / totalTasks) * 100)
              : 0,
            icon: "fa-check",
          },
          {
            title: "Achievements",
            value: `${summary.achievements_earned || 0}/${
              achievements.total || 0
            }`,
            sub: `${summary.total_points || 0} points earned`,
            progress: achievements.total
              ? clampPct(
                  ((summary.achievements_earned || 0) / achievements.total) *
                    100,
                )
              : 0,
            icon: "fa-trophy",
          },
          {
            title: "Current Streak",
            value: `${currentStreak} day${currentStreak === 1 ? "" : "s"}`,
            sub: `Best: ${longestStreak} day${longestStreak === 1 ? "" : "s"}`,
            progress: longestStreak
              ? clampPct((currentStreak / longestStreak) * 100)
              : currentStreak
                ? 100
                : 0,
            icon: "fa-fire",
          },
        ];

        tasks = Array.isArray(data.tasks) ? data.tasks : [];
        recentAchievements = Array.isArray(summary.recent_achievements)
          ? summary.recent_achievements
          : achievements.achievements || [];
        notifications = Array.isArray(data.notifications)
          ? data.notifications
          : [];

        renderSidebar();
        renderStats();
        renderTasks();
        renderAchievements();
        renderNotificationBadge();
        renderActivityChart(data.activity);
      }

      
      function renderStats() {
        statsContainer.innerHTML = "";

        stats.forEach((stat) => {
          const template = statTemplate.content.cloneNode(true);
          const card = template.querySelector(".stat-card");
          const icon = template.querySelector(".icon i");
          const title = template.querySelector("h4");
          const value = template.querySelector("h2");
          const sub = template.querySelector(".sub");
          const fill = template.querySelector(".fill");

          icon.className = `fa-solid ${stat.icon}`;
          title.textContent = stat.title;
          value.textContent = stat.value;
          sub.textContent = stat.sub;
          fill.style.width = stat.progress + "%";

          card.addEventListener("click", () => {
            showToast(stat.title);
          });

          statsContainer.appendChild(template);
        });
      }

      function renderTasks() {
        taskList.innerHTML = "";

        if (!tasks.length) {
          taskList.innerHTML = `
            <div class="empty-state">
              <i class="fa-solid fa-list-check"></i>
              <p>No tasks yet. Start a roadmap to get your first tasks.</p>
            </div>`;
          return;
        }

        tasks.forEach((task) => {
          const template = taskTemplate.content.cloneNode(true);
          const checkbox = template.querySelector("input");
          const title = template.querySelector("h4");
          const date = template.querySelector("small");

          title.textContent = task.title || task.name || "Untitled task";
          date.textContent =
            task.deadline || task.due_date || task.due || "No due date";
          checkbox.checked = Boolean(task.completed ?? task.is_completed);

          checkbox.addEventListener("change", () => {
            task.completed = checkbox.checked;
            showToast(task.completed ? "Task Completed" : "Task Updated");
          });

          taskList.appendChild(template);
        });
      }

     
      function renderAchievements() {
        achievementContainer.innerHTML = "";

        if (!recentAchievements.length) {
          achievementContainer.innerHTML = `
            <div class="empty-state">
              <i class="fa-solid fa-medal"></i>
              <p>No achievements yet. Complete tasks to earn your first badge.</p>
            </div>`;
          return;
        }

        recentAchievements.forEach((achievement) => {
          const template = achievementTemplate.content.cloneNode(true);
          const badge = template.querySelector(".skill");
          const label = template.querySelector(".skill-label");

          const name =
            achievement.name || achievement.title || String(achievement);
          label.textContent = name;

          badge.addEventListener("click", () => {
            showToast(name);
          });

          achievementContainer.appendChild(template);
        });
      }

      
      function renderNotificationBadge() {
        const unread = notifications.filter(
          (n) => n && (n.read === false || n.is_read === false || !n.read_at),
        ).length;

        const count = unread || notifications.length;

        if (count > 0) {
          notifBadge.textContent = count > 9 ? "9+" : String(count);
          notifBadge.classList.add("show");
        } else {
          notifBadge.classList.remove("show");
        }
      }

      
      searchInput.addEventListener("keyup", function () {
        const keyword = this.value.toLowerCase();
        document.querySelectorAll(".task").forEach((task) => {
          const titleEl = task.querySelector("h4");
          if (!titleEl) return;
          const title = titleEl.textContent.toLowerCase();
          task.style.display = title.includes(keyword) ? "flex" : "none";
        });
      });

     
      document.getElementById("floatingBtn").addEventListener("click", () => {
        showToast("Create New Roadmap");
      });

      document.getElementById("resumeBtn").addEventListener("click", () => {
        showToast("Opening your roadmap...");
      });

      document.getElementById("bellBtn").addEventListener("click", () => {
        showToast(
          notifications.length
            ? `${notifications.length} notification${
                notifications.length === 1 ? "" : "s"
              }`
            : "No new notifications",
        );
      });

      chartRange.addEventListener("change", () => {
        renderActivityChart(dashboard ? dashboard.activity : null);
      });

      
      const canvas = document.getElementById("activityChart");
      const ctx = canvas.getContext("2d");

      function drawChart(data) {
        canvas.width = canvas.offsetWidth;
        canvas.height = 300;

        const width = canvas.width;
        const height = canvas.height;

        ctx.clearRect(0, 0, width, height);

        const padding = 40;
        const max = Math.max(...data, 1);
        const gap = (width - padding * 2) / Math.max(data.length - 1, 1);

        /* Grid */
        ctx.strokeStyle = "#E5E7EB";
        ctx.lineWidth = 1;

        for (let i = 0; i < 5; i++) {
          const y = padding + ((height - padding * 2) / 4) * i;
          ctx.beginPath();
          ctx.moveTo(padding, y);
          ctx.lineTo(width - padding, y);
          ctx.stroke();
        }

        /* Line */
        ctx.beginPath();
        ctx.strokeStyle = "#4F46E5";
        ctx.lineWidth = 3;

        data.forEach((value, index) => {
          const x = padding + gap * index;
          const y = height - padding - (value / max) * (height - padding * 2);
          if (index === 0) {
            ctx.moveTo(x, y);
          } else {
            ctx.lineTo(x, y);
          }
        });

        ctx.stroke();

        /* Points */
        data.forEach((value, index) => {
          const x = padding + gap * index;
          const y = height - padding - (value / max) * (height - padding * 2);
          ctx.beginPath();
          ctx.arc(x, y, 5, 0, Math.PI * 2);
          ctx.fillStyle = "#4F46E5";
          ctx.fill();
        });
      }

      function renderActivityChart(activity) {
        const days = Number(chartRange.value) || 7;
        const data = Array.isArray(activity) ? activity.slice(-days) : [];

        if (!data.length) {
          canvas.style.display = "none";
          activityEmpty.style.display = "flex";
          return;
        }

        canvas.style.display = "block";
        activityEmpty.style.display = "none";
        drawChart(data);
      }

     
      function saveDashboard() {
        if (!dashboard) return;
        try {
          localStorage.setItem("career_dashboard", JSON.stringify(dashboard));
        } catch (e) {
          console.log(e);
        }
      }

      function loadDashboard() {
        const cache = localStorage.getItem("career_dashboard");
        if (!cache) return false;

        try {
          dashboard = JSON.parse(cache);
          prepareDashboard(dashboard);
          return true;
        } catch (e) {
          return false;
        }
      }

      
      setInterval(() => {
        saveDashboard();
      }, 5000);


      setInterval(async () => {
        try {
          const fresh = await getDashboard();
          dashboard = fresh;
          prepareDashboard(fresh);
        } catch (e) {
          console.log(e);
        }
      }, 60000);


      window.addEventListener("beforeunload", () => {
        saveDashboard();
      });

      window.addEventListener("resize", () => {
        renderActivityChart(dashboard ? dashboard.activity : null);
      });


      window.addEventListener("load", async () => {
        if (!loadDashboard()) {
          await initializeDashboard();
        } else {
          // Refresh in the background to make sure the cache isn't stale
          initializeDashboard();
        }
      });
  