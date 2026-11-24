data = open("D:/oben code/arap-library.de-fix/plugin/arap-tweaks/arap-tweaks.php", "rb").read().decode("utf-8")
lines = data.split("\n")
bs = [(i + 1, l) for i, l in enumerate(lines) if "\\" in l]
for i, l in bs:
    print(i, l)
print("total backslash lines:", len(bs))
