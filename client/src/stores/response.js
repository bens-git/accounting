import { defineStore } from "pinia";
import { useUserStore } from "./user";
export const useResponseStore = defineStore("response", {
  state: () => ({
    response: {
      success: false,
      message: "",
      errors: [],
    },
  }),

  actions: {
    setResponse(success, message, errors = []) {
      this.response = {
        success,
        message,
        errors,
      };

      // Check if the user is not authenticated
      console.log(message);
      if (message === "Unauthenticated." && !success) {
        const userStore = useUserStore();

        // Clear user data and token
        userStore.logout(); // Assuming you have this method in your user store

        // Clear the response
        this.clearResponse();
      }
    },

    clearResponse() {
      this.response = {
        success: false,
        message: "",
        errors: [],
      };
    },
  },
});
