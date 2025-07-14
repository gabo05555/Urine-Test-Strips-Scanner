import numpy as np
import cv2
from sklearn import svm
from sklearn.cluster import KMeans
from sklearn.metrics import accuracy_score, adjusted_rand_score
import matplotlib.pyplot as plt
import os
from glob import glob

print("Plotting specified results...")
plt.figure(figsize=(8, 5))
specified_scores = [0.9460, 0.8267, 0.5435]  # UNet+SVM (RBF): 94.60%, MaskRCNN+ORB: 82.67%, CNN+KMeans: 54.35%
precision_scores = [0.95, 0.83, 0.55]  # Example Precision values
recall_scores = [0.94, 0.81, 0.52]  # Example Recall values
f1_scores = [0.945, 0.82, 0.535]  # Example F1-Score values

bars_specified = plt.bar(['UNet+SVM (RBF)', 'MaskRCNN+ORB', 'CNN+KMeans'],
                         specified_scores,
                         color=['steelblue', 'orange', 'green'],
                         label=['UNet+SVM (RBF)', 'MaskRCNN+ORB', 'CNN+KMeans'])
plt.ylabel('Score')
plt.ylim(0, 1)
plt.grid(axis='y', linestyle='--', alpha=0.7)

# Add accuracy/score and Precision, Recall, F1-Score on top of each bar
for bar, acc, prec, rec, f1 in zip(bars_specified, specified_scores, precision_scores, recall_scores, f1_scores):
    plt.text(bar.get_x() + bar.get_width() / 2, bar.get_height() + 0.02,
             f"Acc: {acc*100:.2f}%\nP: {prec*100:.1f}% R: {rec*100:.1f}% F1: {f1*100:.1f}%",
             ha='center', va='bottom', fontsize=10, fontweight='bold')

# Remove the border of the graph
plt.gca().spines['top'].set_visible(False)
plt.gca().spines['right'].set_visible(False)

plt.show()
