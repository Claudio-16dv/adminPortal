'use client';

import '../globals.css';
import { ToastContainer } from 'react-toastify';
import { useRouter } from 'next/navigation';
import { useEffect, useState } from 'react';
import 'react-toastify/dist/ReactToastify.css';

export default function PrivateLayout({ children }) {
  const router = useRouter();
  const [isAuthorized, setIsAuthorized] = useState(null);

  useEffect(() => {
    const token = localStorage.getItem("token");
    setIsAuthorized(!!token);
  }, []);

  const handleLogout = async () => {
    await fetch("http://localhost:8000/auth/logout", {
      method: "POST",
      credentials: "include",
    });
    localStorage.removeItem("token");
    router.push("/");
  };

  if (isAuthorized === false) {
    return (
      <div style={{
        minHeight: "100vh",
        display: "flex",
        flexDirection: "column",
        justifyContent: "center",
        alignItems: "center",
        backgroundColor: "#f3f4f6",
        color: "#dc2626",
        textAlign: "center",
        padding: "2rem",
      }}>
        <h1 style={{ fontSize: "2rem", fontWeight: "bold" }}>403 - Acesso Negado</h1>
        <p>Você não tem permissão para entrar nessa página sem login.</p>
      </div>
    );
  }

  if (isAuthorized === null) return null;

  return (
    <>
      <div style={{
        position: "fixed",
        top: "1rem",
        right: "1rem",
        zIndex: 1000,
      }}>
        <button onClick={handleLogout} style={{
          backgroundColor: "#174ec5",
          color: "#fff",
          border: "none",
          borderRadius: "0.375rem",
          padding: "0.5rem 1rem",
          cursor: "pointer",
          fontWeight: "bold"
        }}>
          Sair
        </button>
      </div>

      {children}

      <ToastContainer position="top-right" autoClose={3000} hideProgressBar />
    </>
  );
}
