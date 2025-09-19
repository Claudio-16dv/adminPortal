"use client";

import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import styles from "../../page.module.css"; 
import { toast } from "react-toastify";
import { IMaskInput } from "react-imask";

export default function EditClient() {
  const router = useRouter();

  const [client, setClient] = useState(null);      
  const [original, setOriginal] = useState(null);  
  const [loading, setLoading] = useState(true);
  const [currentId, setCurrentId] = useState(null);

  const onlyDigits = (v = "") => v.replace(/\D/g, "");

  const isoToDMY = (value = "") => {
    if (!value) return "";
    const m = String(value).match(/^(\d{4})-(\d{2})-(\d{2})/);
    if (m) return `${m[3]}/${m[2]}/${m[1]}`;
    return String(value);
  };

  const dmyToISO = (value = "") => {
    if (!value) return "";
    const m = String(value).match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
    if (m) return `${m[3]}-${m[2]}-${m[1]}`;
    return String(value);
  };

  const handleChange = (field, value) => {
    setClient((prev) => ({ ...prev, [field]: value }));
  };

  const handleAddrChange = (index, field, value) => {
    setClient((prev) => {
      const updated = [...(prev?.addresses || [])];
      updated[index] = { ...updated[index], [field]: value };
      return { ...prev, addresses: updated };
    });
  };

  const handleAddAddress = () => {
    setClient((prev) => ({
      ...prev,
      addresses: [
        ...(prev?.addresses || []),
        {
          street: "",
          number: "",
          neighborhood: "",
          city: "",
          state: "",
          zip_code: "",
          complement: "",
        },
      ],
    }));
  };

  const handleRemoveNewAddress = (index) => {
    setClient((prev) => {
      const updated = [...prev.addresses];
      if (!updated[index]?.id) updated.splice(index, 1);
      return { ...prev, addresses: updated };
    });
  };

  const handleCepChange = async (index, value) => {
    handleAddrChange(index, "zip_code", value);
    const cep = onlyDigits(value);
    if (cep.length === 8) {
      try {
        const res = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
        const data = await res.json();
        if (!data.erro) {
          handleAddrChange(index, "street", data.logradouro || "");
          handleAddrChange(index, "neighborhood", data.bairro || "");
          handleAddrChange(index, "city", data.localidade || "");
          handleAddrChange(index, "state", (data.uf || "").toUpperCase());
        }
      } catch (e) {
        console.error("Erro ao buscar CEP:", e);
      }
    }
  };

  const hasInvalidNewAddress = () => {
    if (!Array.isArray(client?.addresses)) return false;
    return client.addresses.some((addr) => {
      if (!addr?.id) {
        return (
          !addr.street?.trim() ||
          !addr.number?.trim() ||
          !addr.neighborhood?.trim() ||
          !addr.city?.trim() ||
          !addr.state?.trim() ||
          !addr.zip_code?.trim()
        );
      }
      return false;
    });
  };

  const hasChanges = () => {
    if (!client || !original) return false;
    return JSON.stringify(client) !== JSON.stringify(original);
  };

  const loadClient = async (id) => {
    try {
      const res = await fetch(`http://localhost:8000/clients/edit/${id}`, {
        credentials: "include",
        headers: { "Content-Type": "application/json" },
      });
      if (res.status === 401) {
        router.push("/");
        return;
      }
      const data = await res.json();

      const safe = {
        id: data.id,
        name: data.name ?? "",
        birthdate: isoToDMY(data.birthdate ?? ""),
        cpf: data.cpf ?? "",
        rg: data.rg ?? "",
        phone: data.phone ?? "",
        addresses: Array.isArray(data.addresses)
          ? data.addresses.map((a) => ({ ...a, state: (a.state || "").toUpperCase() }))
          : [],
      };

      setClient(safe);
      setOriginal(safe);
    } catch (e) {
      console.error(e);
      toast.error("Falha ao carregar cliente.");
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    const sid = typeof window !== "undefined"
      ? sessionStorage.getItem("editClientId")
      : null;

    if (!sid) {
      toast.info("Selecione um cliente para editar.");
      router.replace("/clientes");
      return;
    }

    setCurrentId(sid);
    loadClient(sid);
  }, []);

  const handleSave = async () => {
    if (!client?.name || !client?.birthdate || !client?.cpf || !client?.rg || !client?.phone) {
      toast.error("Preencha os campos obrigatórios.");
      return;
    }
    if (hasInvalidNewAddress()) {
      toast.error("Preencha todos os campos dos novos endereços antes de salvar.");
      return;
    }

    const payload = {
      ...client,
      birthdate: dmyToISO(client.birthdate),
      cpf: onlyDigits(client.cpf),
      phone: onlyDigits(client.phone),
      addresses: (client.addresses || []).map((a) => ({
        ...a,
        state: (a.state || "").toUpperCase(),
        zip_code: onlyDigits(a.zip_code || ""),
      })),
    };

    try {
      const res = await fetch(`http://localhost:8000/clients/update/${client.id}`, {
        method: "PUT",
        credentials: "include",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
      });
      const data = await res.json().catch(() => ({}));
      if (!res.ok) throw new Error(data.message || data.error || "Erro ao salvar alterações.");

      toast.success("Cliente atualizado com sucesso!");
      await loadClient(currentId);
    } catch (e) {
      toast.error(e.message || "Erro inesperado ao salvar.");
    }
  };

  if (loading) return <p className={styles.loadingText}>Carregando…</p>;
  if (!client) return <p className={styles.errorMsg}>Cliente não encontrado.</p>;

  return (
    <div className={styles.wrapperMain}>
      <div className={styles.clientContainer}>
        <h1 className={styles.headingMain}>Editar Cliente</h1>

        <IMaskInput
          mask={/^[\s\S]*$/}
          value={client.name}
          onAccept={(v) => handleChange("name", v)}
          className={styles.inputField}
          placeholder="Nome"
        />

        <IMaskInput
          mask="00/00/0000"
          value={client.birthdate}
          onAccept={(v) => handleChange("birthdate", v)}
          className={styles.inputField}
          placeholder="Data de Nascimento (dd/mm/aaaa)"
          inputMode="numeric"
        />

        <IMaskInput
          mask="000.000.000-00"
          value={client.cpf}
          onAccept={(v) => handleChange("cpf", v)}
          className={styles.inputField}
          placeholder="CPF"
          inputMode="numeric"
        />

        <IMaskInput
          mask={/^[\s\S]*$/}
          value={client.rg}
          onAccept={(v) => handleChange("rg", v)}
          className={styles.inputField}
          placeholder="RG"
        />

        <IMaskInput
          mask="(00) 00000-0000"
          value={client.phone}
          onAccept={(v) => handleChange("phone", v)}
          className={styles.inputField}
          placeholder="Telefone"
          inputMode="numeric"
        />

        {/* Endereços */}
        <h3 className={styles.addressTitle}>Endereços</h3>
        {(client.addresses || []).map((addr, idx) => (
          <div key={addr.id ?? `new-${idx}`} className={styles.addressWrapper}>
            <div className={styles.addressHeader}>
              <span>Endereço {idx + 1}</span>
              {!addr.id && (
                <span
                  className={styles.closeNewAddress}
                  onClick={() => handleRemoveNewAddress(idx)}
                  title="Remover novo endereço"
                >
                  ✕
                </span>
              )}
            </div>

            <div className={styles.addressDetails}>
              <IMaskInput
                mask="00000-000"
                value={addr.zip_code || ""}
                onAccept={(v) => handleCepChange(idx, v)}
                className={styles.inputField}
                placeholder="CEP"
                inputMode="numeric"
              />
              <IMaskInput
                mask={/^[\s\S]*$/}
                value={addr.street || ""}
                onAccept={(v) => handleAddrChange(idx, "street", v)}
                className={styles.inputField}
                placeholder="Rua"
              />
              <IMaskInput
                mask={/^[\s\S]*$/}
                value={addr.number || ""}
                onAccept={(v) => handleAddrChange(idx, "number", v)}
                className={styles.inputField}
                placeholder="Número"
              />
              <IMaskInput
                mask={/^[\s\S]*$/}
                value={addr.neighborhood || ""}
                onAccept={(v) => handleAddrChange(idx, "neighborhood", v)}
                className={styles.inputField}
                placeholder="Bairro"
              />
              <IMaskInput
                mask={/^[\s\S]*$/}
                value={addr.city || ""}
                onAccept={(v) => handleAddrChange(idx, "city", v)}
                className={styles.inputField}
                placeholder="Cidade"
              />
              <IMaskInput
                mask={/^[A-Za-z]{0,2}$/}
                value={(addr.state || "").toUpperCase()}
                onAccept={(v) => handleAddrChange(idx, "state", v.toUpperCase())}
                className={styles.inputField}
                placeholder="UF"
              />
              <IMaskInput
                mask={/^[\s\S]*$/}
                value={addr.complement || ""}
                onAccept={(v) => handleAddrChange(idx, "complement", v)}
                className={styles.inputField}
                placeholder="Complemento"
              />
            </div>
          </div>
        ))}

        <div className={styles.buttonGroup}>
          <button className={styles.btnAction} onClick={handleAddAddress}>
            Adicionar Endereço
          </button>
          <button
            className={styles.btnSave}
            disabled={!hasChanges() || hasInvalidNewAddress()}
            onClick={handleSave}
          >
            Salvar Alterações
          </button>
        </div>

        <button className={styles.menuBtn} onClick={() => router.back()}>
          Voltar
        </button>
      </div>
    </div>
  );
}
