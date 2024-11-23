import { createRouter, createWebHistory } from "vue-router";
import EmailVerified from "@/components/EmailVerified.vue";
import EditUser from "@/components/EditUser.vue";
import LoginForm from "@/components/LoginForm.vue";
import TransactionList from "@/components/TransactionList.vue";
import { useUserStore } from "@/stores/user"; // Adjust the import path as necessary

const routes = [
  {
    path: "/transaction-list",
    name: "transaction-list",
    component: TransactionList,
    meta: { requiresAuth: true },
  },

  {
    path: "/login-form",
    name: "login-form",
    component: LoginForm,
    meta: { requiresGuest: true }, // Only accessible if not logged in
  },

  {
    path: "/register-form",
    component: () => import("@/components/RegisterForm.vue"),
    meta: { requiresGuest: true }, // Only accessible if not logged in
  },
  {
    path: "/request-password-reset-form",
    component: () => import("@/components/RequestPasswordResetForm.vue"),
    meta: { requiresGuest: true }, // Only accessible if not logged in
  },
  {
    path: "/reset-password",
    component: () => import("@/components/ResetPassword.vue"),
    meta: { requiresGuest: true }, // Only accessible if not logged in
  },
  {
    path: "/edit-user",
    name: "edit-user",
    component: EditUser,
    meta: { requiresAuth: true },
  },

  {
    path: "/:catchAll(.*)",
    redirect: "/transaction-list",
  },
  {
    path: "/email-verified",
    name: "EmailVerified",
    component: EmailVerified,
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach((to, from, next) => {
  const userStore = useUserStore();
  const isAuthenticated = !!userStore.user; // Check if user is logged in
  const isDiscordAuthenticated =
    !!userStore.user && !!userStore.user.discord_user_id;
  const requiresAuth = to.meta.requiresAuth;
  const requiresDiscord = to.meta.requiresDiscord;

  if (
    to.matched.some((record) => record.meta.requiresGuest) &&
    userStore.user
  ) {
    // Redirect to home page if user is already logged in
    return next({ name: "transaction-list" });
  }

  if (requiresAuth && !isAuthenticated) {
    // Redirect to login if route requires authentication and user is not logged in
    next({ name: "login-form" });
  } else {
    next(); // Proceed to the route
  }
});

export default router;
