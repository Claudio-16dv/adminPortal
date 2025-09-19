"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import styles from "../../page.module.css";
import { toast } from "react-toastify";
import { IMaskInput } from "react-imask";

export default function CadastrarCliente() {
  const router = useRouter();
  const [client, setClient] = useState({
    name: "",
    birthdate: "",
    cpf: "",
    rg: "",
    phone: "",
    addresses: [
      {
        street: "",
        number: "",
        neighborhood: "",
        city: "",
        state: "",
        zip_code: "",
        complement: ""
      }
    ]
  });

  const handleChange = (field, value) => {
    setClient((prev) => ({ ...prev, [field]: value }));
  };

  const handleAddrChange = (index, field, value) => {
    const updated = [...client.addresses];
    updated[index][field] = value;
    setClient((prev) => ({ ...prev, addresses: updated }));
  };

  const handleAddAddress = () => {
    setClient((prev) => ({
      ...prev,
      addresses: [
        ...prev.addresses,
        {
          street: "",
          number: "",
          neighborhood: "",
          city: "",
          state: "",
          zip_code: "",
          complement: ""
        }
      ]
    }));
  };

  const handleRemoveAddress = (index) => {
    const updated = [...client.addresses];
    updated.splice(index, 1);
    setClient((prev) => ({ ...prev, addresses: updated }));
  };

  const hasEmptyFields = () => {
    if (!client.name || !client.birthdate || !client.cpf || !client.rg || !client.phone) return true;
    return client.addresses.some(
      (addr) => !addr.street || !addr.number || !addr.neighborhood || !addr.city || !addr.state || !addr.zip_code
    );
  };

  const handleCepChange = async (idx, value) => {
    handleAddrChange(idx, "zip_code", value);

    const cleanCep = value.replace(/\D/g, "");
    if (cleanCep.length === 8) {
      try {
        const res = await fetch(`https://viacep.com.br/ws/${cleanCep}/json/`);
        const data = await res.json();
        if (!data.erro) {
          handleAddrChange(idx, "street", data.logradouro || "");
          handleAddrChange(idx, "neighborhood", data.bairro || "");
          handleAddrChange(idx, "city", data.localidade || "");
          handleAddrChange(idx, "state", data.uf || "");
        }
      } catch (e) {
        console.error("Erro ao buscar CEP", e);
      }
    }
  };

  const handleSubmit = async () => {
    if (hasEmptyFields()) {
      toast.error("Preencha todos os campos obrigatórios.");
      return;
    }
    try {
        const res = await fetch("http://localhost:8000/clients/create", {
            method: "POST",
            headers: {
            "Content-Type": "application/json"
            },
            credentials: "include",
            body: JSON.stringify({
            name: client.name,
            birthdate: client.birthdate,
            cpf: client.cpf,
            rg: client.rg,
            phone: client.phone,
            addresses: client.addresses
            })
        });
        const data = await res.json();

        if (!res.ok) {
            throw new Error(data.message || data.error || "Erro ao cadastrar cliente.");
        }
        
          toast.success("Cliente cadastrado com sucesso!");
          router.push("/home");
        } catch (err) {
            toast.error(err.message || "Erro inesperado.");
    }
  };

  return (
    <div className={styles.wrapperMain}>
      <div className={styles.clientContainer}>
        <h1 className={styles.headingMain}>Cadastrar Cliente</h1>

        <input className={styles.inputField} placeholder="Nome" value={client.name} onChange={(e) => handleChange("name", e.target.value)} />
        <input type="date" className={styles.inputField} placeholder="Data de Nascimento" value={client.birthdate} onChange={(e) => handleChange("birthdate", e.target.value)}/>
        
        <IMaskInput
          mask="000.000.000-00"
          value={client.cpf}
          onAccept={(value) => handleChange("cpf", value)}
          className={styles.inputField}
          placeholder="CPF"
          inputMode="numeric"
        />

        <input className={styles.inputField} placeholder="RG" value={client.rg} onChange={(e) => handleChange("rg", e.target.value)} />
        
        <IMaskInput
          mask="(00) 00000-0000"
          value={client.phone}
          onAccept={(value) => handleChange("phone", value)}
          className={styles.inputField}
          placeholder="(99) 99999-9999"
          inputMode="numeric"
        />

        <h3 className={styles.addressTitle}>Endereços</h3>
        {client.addresses.map((addr, idx) => (
          <div key={idx} className={styles.addressWrapper}>
            <div className={styles.addressHeader}>
              <span>{addr.street || "Novo endereço"}</span>
              {client.addresses.length > 1 && (
                <span className={styles.closeNewAddress} onClick={() => handleRemoveAddress(idx)}>✕</span>
              )}
            </div>
            <div className={styles.addressDetails}>
              <IMaskInput
                mask="00000-000"
                value={addr.zip_code}
                onAccept={(value) => handleCepChange(idx, value)}
                className={styles.inputField}
                placeholder="CEP"
                inputMode="numeric"
              />
              <input className={styles.inputField} placeholder="Rua" value={addr.street} onChange={(e) => handleAddrChange(idx, "street", e.target.value)} />
              <input className={styles.inputField} placeholder="Número" value={addr.number} onChange={(e) => handleAddrChange(idx, "number", e.target.value)} />
              <input className={styles.inputField} placeholder="Bairro" value={addr.neighborhood} onChange={(e) => handleAddrChange(idx, "neighborhood", e.target.value)} />
              <input className={styles.inputField} placeholder="Cidade" value={addr.city} onChange={(e) => handleAddrChange(idx, "city", e.target.value)} />
              <input className={styles.inputField} placeholder="UF" value={addr.state} onChange={(e) => handleAddrChange(idx, "state", e.target.value)} />
              <input className={styles.inputField} placeholder="Complemento" value={addr.complement} onChange={(e) => handleAddrChange(idx, "complement", e.target.value)} />
            </div>
          </div>
        ))}

        <div className={styles.buttonGroup}>
          <button className={styles.btnAction} onClick={handleAddAddress}>Adicionar Endereço</button>
          <button className={styles.btnSave} onClick={handleSubmit}>Salvar Cliente</button>
        </div>
        <button className={styles.menuBtn} onClick={() => router.push("/home")}>
          Voltar
        </button>
      </div>
    </div>
  );
}
