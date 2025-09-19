"use client";

import { useRouter } from "next/navigation";
import styles from "../../page.module.css";

export default function MenuPage() {
  const router = useRouter();

  return (
    <div className={styles.menuWrapper}>
      <div className={styles.menuCard}>
        <h1 className={styles.menuTitle}>Menu </h1>
        <button className={styles.menuBtn} onClick={() => router.push("/clientes")}>
          Clientes
        </button>
        <button className={styles.menuBtn} onClick={() => router.push("/cadastro_cliente")}>
          Cadastrar Novo Cliente
        </button>
      </div>
    </div>
  );
}