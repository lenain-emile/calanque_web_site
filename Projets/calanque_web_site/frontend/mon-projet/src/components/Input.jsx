import "../styles/Input.css";

const Input = ({
  label,
  type = "text",
  value,
  onChange,
  placeholder,
  required = false,
  disabled = false,
  error,
  className = "",
  ...props
}) => {
  const inputClasses = [
    "input-field",
    error ? "input-error" : "",
    className
  ].filter(Boolean).join(" ");

  return (
    <div className="input-container">
      {label && (
        <label className="input-label">
          {label}
          {required && <span className="input-required">*</span>}
        </label>
      )}
      <input
        type={type}
        value={value}
        onChange={onChange}
        placeholder={placeholder}
        required={required}
        disabled={disabled}
        className={inputClasses}
        {...props}
      />
      {error && (
        <div className="input-error-message">
          {error}
        </div>
      )}
    </div>
  );
};

export default Input;
