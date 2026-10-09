import "../css/app.css";
import React from "react";
import { createRoot } from "react-dom/client";
import { BrowserRouter, Routes, Route } from "react-router-dom";
import { FinanceProvider } from "./context/financecontext";
import { applyTheme, getStoredTheme } from "./utils/theme";
import Sidebar from "./components/sidebar";
import ReminderPopup from "./components/reminderpopup";
import AuthModal from "./components/authmodal";
import Dashboard from "./pages/dashboard";
import Transactions from "./pages/transaction";
import Goals from "./pages/goals";
import Profile from "./pages/profile";

applyTheme(getStoredTheme());

function App() {
    return (
        <FinanceProvider>
            <BrowserRouter>
                <div className="min-h-screen bg-bg">
                    <Sidebar />
                    <Routes>
                        <Route path="/" element={<Dashboard />} />
                        <Route
                            path="/transactions"
                            element={<Transactions />}
                        />
                        <Route path="/goals" element={<Goals />} />
                        <Route path="/profile" element={<Profile />} />
                    </Routes>
                    <ReminderPopup />
                    <AuthModal />
                </div>
            </BrowserRouter>
        </FinanceProvider>
    );
}

const root = createRoot(document.getElementById("app"));
root.render(<App />);
