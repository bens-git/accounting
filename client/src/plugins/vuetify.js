// Styles
import "@mdi/font/css/materialdesignicons.css";
import "vuetify/styles";

// Vuetify
import { createVuetify } from "vuetify";
import * as directives from "vuetify/directives";
import { VDateInput } from "vuetify/labs/VDateInput";

const myCustomLightTheme = {
  dark: {
    colors: {
      primary: "#82B1FF", // Lighter blue for dark mode
      secondary: "#A1A1A1", // Softer grey
      accent: "#FFC107", // Consistent yellow
      success: "#81C784", // Lighter green
      error: "#FF6F61", // Softer red
      warning: "#FFA726", // Lighter orange
      info: "#64B5F6", // Lighter blue
      background: "#121212", // Dark background
      surface: "#1E1E1E", // Dark card surface
    },
  },
};

export default createVuetify(
  // https://vuetifyjs.com/en/introduction/why-vuetify/#feature-guides
  {
    theme: {
      defaultTheme: "dark",
      themes: {
        dark: {
          colors: {
            primary: "#1A73E8", // Professional dark blue for main actions
            secondary: "#1E1E2F", // Dark grey-blue for sidebars or headers
            accent: "#FFB300", // Gold for highlights or key financial indicators
            success: "#4CAF50", // Green for positive trends (profits, gains)
            error: "#FF5252", // Red for errors or losses
            warning: "#FF9800", // Orange for warnings
            info: "#29B6F6", // Light blue for informational elements
            background: "#121212", // Deep dark grey for the app background
            surface: "#1E1E2F", // Slightly lighter grey for cards or tables
            text: "#E8EAF6", // Soft white for readable text
          },
          variables: {
            // Add any custom CSS variables for advanced theming
            rounded: "4px", // Slightly rounded corners for a modern look
            borderOpacity: 0.1, // Subtle borders
          },
        },
      },
    },
    // locale: 'en-CA',
    components: { VDateInput },
    directives,
  }
);
