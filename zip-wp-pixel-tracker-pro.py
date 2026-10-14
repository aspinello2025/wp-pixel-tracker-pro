import os
import zipfile

def zip_plugin():
    source_dir = os.path.join(os.path.dirname(__file__), 'wp-pixel-tracker-pro')
    output_filename = os.path.join(os.path.dirname(__file__), 'wp-pixel-tracker-pro.zip')

    if os.path.exists(output_filename):
        os.remove(output_filename)

    with zipfile.ZipFile(output_filename, 'w', zipfile.ZIP_DEFLATED) as zipf:
        for root, dirs, files in os.walk(source_dir):
            for file in files:
                file_path = os.path.join(root, file)
                arcname = os.path.relpath(file_path, os.path.dirname(source_dir))
                zipf.write(file_path, arcname)
                print(f"Adicionado: {arcname}")

    print(f"\nSucesso! Pacote zip limpo criado em: {output_filename}")

if __name__ == '__main__':
    zip_plugin()
